<?php

namespace App\Console\Commands;

use App\Models\Crop;
use App\Models\CropSeason;
use App\Models\ExpenseCategory;
use App\Models\Field;
use App\Models\SeasonStage;
use App\Models\Unit;
use App\Models\User;
use App\Services\ExpenseService;
use App\Services\ProductionService;
use App\Services\SeasonService;
use DateTimeInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use OpenSpout\Common\Entity\Cell\FormulaCell;
use OpenSpout\Reader\XLSX\Options;
use OpenSpout\Reader\XLSX\Reader;

/**
 * Imports a crop-season cost register workbook (HAL "Master_Register" layout) into one crop season,
 * then reconciles the software's totals against the workbook's own analysis sheets.
 */
class ImportSeasonRegister extends Command
{
    protected $signature = 'season:import-register
        {file : Path to the .xlsx workbook}
        {--farm=1 : Farm id to import into}
        {--name=Maize 2026 – Faisalabad (HAL) : Name for the new season}
        {--compare= : Skip the import and only reconcile this existing season code against the workbook}';

    protected $description = 'Create a crop season from a cost-register workbook and reconcile it with the workbook reports';

    /** Workbook stage code => software stage template code. ST04 is split by account (see stageFor). */
    private const STAGES = ['ST01' => 'LAND_PREP', 'ST02' => 'SEED_MAT', 'ST03' => 'GROWING', 'ST04' => 'HARVEST'];

    /** Workbook account code => [parent category, sub-category, cost type, default unit]. */
    private const ACCOUNTS = [
        '2101' => ['Seed', 'Hybrid maize seed', 'material', 'bag'],
        '2103' => ['Labour', 'Sowing & planting labour', 'labour', 'lumpsum'],
        '3101' => ['Fertilizer', 'Urea', 'material', 'bag'],
        '3104' => ['Fertilizer', 'Potash (SOP)', 'material', 'bag'],
        '3108' => ['Fertilizer', 'Farmyard manure', 'material', 'trolley'],
        '4104' => ['Crop protection', 'Pesticide (unclassified)', 'material', 'bottle'],
        '4105' => ['Crop protection', 'Spraying charges & fuel', 'service', 'litre'],
        '6106' => ['Labour', 'Labour food', 'labour', 'lumpsum'],
        '7102' => ['Fuel', 'Diesel', 'fuel', 'litre'],
        '7105' => ['Maintenance', 'Tractor repair', 'maintenance', 'unit'],
        '8103' => ['Machinery hire', 'Harvesting, chopping & ensiling', 'machinery', 'acre'],
        '8108' => ['Silage materials', 'Plastic sheet', 'material', 'roll'],
        '9101' => ['Transport', 'Input transportation', 'transport', 'lumpsum'],
    ];

    /** Workbook unit => [software unit code, name, kind]. */
    private const UNITS = [
        'trolley' => ['trolley', 'Trolley', 'trip'],
        'litre' => ['litre', 'Litre', 'volume'],
        'no.' => ['no', 'Number (No.)', 'count'],
        'lump sum' => ['lumpsum', 'Lump sum', 'count'],
        'bag' => ['bag', 'Bag', 'count'],
        'bottle' => ['bottle', 'Bottle', 'count'],
        'packet' => ['packet', 'Packet', 'count'],
        'rolls' => ['roll', 'Roll', 'count'],
        'acre' => ['acre', 'Acre', 'area'],
    ];

    public function handle(SeasonService $seasons, ExpenseService $expenses, ProductionService $production): int
    {
        $file = $this->argument('file');
        if (! is_file($file)) {
            $this->error("File not found: {$file}");

            return self::FAILURE;
        }

        $owner = User::query()->where('farm_id', $this->option('farm'))->where('role', 'owner')->first();
        if (! $owner) {
            $this->error('No owner user found for farm '.$this->option('farm'));

            return self::FAILURE;
        }
        Auth::setUser($owner);

        $book = $this->readWorkbook($file);
        $register = $this->register($book['Master_Register']);
        $this->info(sprintf('Read %d register lines totalling Rs %s.', $register->count(), number_format($register->sum('amount'), 4)));

        if ($code = $this->option('compare')) {
            $season = CropSeason::query()->where('code', $code)->firstOrFail();
        } else {
            if (CropSeason::query()->where('name', $this->option('name'))->exists()) {
                $this->error('A season named "'.$this->option('name').'" already exists. Use --compare=<code> to reconcile it.');

                return self::FAILURE;
            }
            $season = DB::transaction(fn () => $this->import($book, $register, $seasons, $expenses, $production));
            $this->info("Created season {$season->code} ({$season->name}).");
        }

        $ok = $this->reconcile($season->fresh(), $register, $book);
        Auth::forgetUser();

        return $ok ? self::SUCCESS : self::FAILURE;
    }

    private function import(array $book, Collection $register, SeasonService $seasons, ExpenseService $expenses, ProductionService $production): CropSeason
    {
        $units = $this->ensureUnits();
        $categories = $this->ensureCategories();
        $fields = $this->ensureFields($book['Land_Master']);
        $acres = $fields->sum(fn (Field $f) => (float) $f->area_acres);

        $season = $seasons->create([
            'name' => $this->option('name'),
            'crop_id' => Crop::query()->where('name', 'Maize')->value('id'),
            'start_date' => $register->min('date'),
            'expected_end_date' => null,
            'fields' => $fields->mapWithKeys(fn (Field $f) => [$f->id => $f->area_acres])->all(),
            'notes' => Str::limit(implode(' ', [
                'Imported from the HAL Maize Crop 2026 Cost Accounting Dashboard (Rev1).',
                "Faisalabad, {$acres} acres, costs recorded whole-crop (no field-level split); parcels L01–L14 are provisional 1-acre units.",
                'Each expense description starts with its workbook Txn ID. Production is provisional at the 500 maund/acre benchmark (280,000 kg) until measured.',
            ]), 1000),
        ]);

        $stages = SeasonStage::query()->where('season_id', $season->id)->with('template')->get()->keyBy(fn ($s) => $s->template->code);

        foreach ($register as $row) {
            $unit = $units[strtolower($row['unit'])] ?? throw new \RuntimeException("{$row['txn']}: unknown unit {$row['unit']}");
            $expenses->save([
                'season_stage_id' => $stages[$this->stageFor($row)]->id,
                'category_id' => $categories[$row['account']] ?? throw new \RuntimeException("{$row['txn']}: unmapped account {$row['account']}"),
                'expense_date' => $row['date'],
                'description' => $this->describe($row),
                'quantity' => $row['qty'],
                'unit_code' => $unit,
                'unit_price' => $row['rate'],
                'amount' => round($row['amount'], 2),
            ]);
        }

        foreach ($stages as $code => $stage) {
            $lines = $register->filter(fn ($r) => $this->stageFor($r) === $code);
            if ($lines->isEmpty()) {
                continue;
            }
            $stage->update([
                'start_date' => $lines->min('date'),
                'end_date' => $code === 'SILAGE' ? null : $lines->max('date'),
                'status' => $code === 'SILAGE' ? 'in_progress' : 'completed',
                'notes' => 'Workbook activities: '.$lines->pluck('detail')->unique()->implode(', ').'.',
            ]);
        }

        // Provisional production from the Silage_Unit_Cost sheet (benchmark until actual is measured).
        $kg = $this->provisionalKg($book['Silage_Unit_Cost']);
        if ($kg > 0) {
            $production->saveHarvest($season, [
                'harvest_date' => $register->where('account', '8103')->max('date'),
                'field_id' => null,
                'method' => 'estimate',
                'quantity_kg' => $kg,
            ]);
        }

        return $season;
    }

    /** ST04 holds both the harvesting contract and the silage materials; the software keeps those in separate stages. */
    private function stageFor(array $row): string
    {
        if ($row['stage'] === 'ST04') {
            return $row['account'] === '8108' ? 'SILAGE' : 'HARVEST';
        }

        return self::STAGES[$row['stage']] ?? throw new \RuntimeException("{$row['txn']}: unknown stage {$row['stage']}");
    }

    private function describe(array $row): string
    {
        $parts = [$row['txn'].' – '.$row['description']];
        if ($row['rework']) {
            $parts[] = 'Re-work';
        }
        if ($row['completeness'] !== 'Complete') {
            $parts[] = $row['completeness'].' record';
        }
        if ($row['remarks']) {
            $parts[] = $row['remarks'];
        }

        return Str::limit(implode(' · ', $parts), 200, '…');
    }

    private function ensureUnits(): array
    {
        $map = [];
        foreach (self::UNITS as $label => [$code, $name, $kind]) {
            Unit::query()->firstOrCreate(['code' => $code], ['name' => $name, 'kind' => $kind]);
            $map[$label] = $code;
        }

        return $map;
    }

    private function ensureCategories(): array
    {
        $farmId = Auth::user()->farm_id;
        $map = [];
        foreach (self::ACCOUNTS as $account => [$parentName, $childName, $type, $unit]) {
            $parent = ExpenseCategory::query()->whereNull('parent_id')->where('name', $parentName)->firstOrFail();
            $child = ExpenseCategory::query()->where('parent_id', $parent->id)->where('name', $childName)->first();
            if (! $child) {
                $child = new ExpenseCategory(['parent_id' => $parent->id, 'name' => $childName, 'cost_type' => $type, 'default_unit' => $unit, 'is_active' => true]);
                $child->farm_id = $farmId;
                $child->save();
                $this->line("  + category {$parentName} → {$childName}");
            }
            $map[$account] = $child->id;
        }

        return $map;
    }

    private function ensureFields(array $rows): Collection
    {
        $fields = collect();
        foreach (array_slice($rows, 3) as $row) {
            $code = trim((string) ($row[0] ?? ''));
            if (! preg_match('/^L\d+$/', $code)) {
                continue;
            }
            $name = "HAL {$code} – ".trim((string) $row[1]);
            $fields->push(Field::query()->firstOrCreate(['name' => $name], ['area_acres' => (float) $row[2], 'is_active' => true]));
        }

        return $fields;
    }

    private function provisionalKg(array $rows): float
    {
        foreach ($rows as $row) {
            if (str_starts_with(trim((string) ($row[1] ?? '')), 'Provisional production')) {
                return (float) ($row[3] ?? 0);
            }
        }

        return 0;
    }

    /** @return array<string, list<list<mixed>>> */
    private function readWorkbook(string $file): array
    {
        $reader = new Reader(new Options(SHOULD_PRESERVE_EMPTY_ROWS: true));
        $reader->open($file);
        $book = [];
        foreach ($reader->getSheetIterator() as $sheet) {
            $rows = [];
            foreach ($sheet->getRowIterator() as $row) {
                $rows[] = array_map(fn ($cell) => $cell instanceof FormulaCell ? $cell->getComputedValue() : $cell->getValue(), $row->cells);
            }
            $book[$sheet->getName()] = $rows;
        }
        $reader->close();

        return $book;
    }

    private function register(array $rows): Collection
    {
        $header = array_map(fn ($h) => trim((string) $h), $rows[2]);
        $col = fn (string $name) => ($i = array_search($name, $header, true)) === false ? throw new \RuntimeException("Missing column {$name}") : $i;
        $c = collect(['Txn ID', 'Date', 'Stage Code', 'Stage Detail', 'Account Code', 'Account Head', 'Description', 'Product / Input', 'Qty', 'Unit', 'Unit Rate', 'Total Amount', 'Txn Type', 'Rework', 'Completeness', 'Remarks'])
            ->mapWithKeys(fn ($n) => [$n => $col($n)]);

        return collect(array_slice($rows, 3))
            ->filter(fn ($r) => preg_match('/^TX\d+$/', trim((string) ($r[$c['Txn ID']] ?? ''))))
            ->map(fn ($r) => [
                'txn' => trim($r[$c['Txn ID']]),
                'date' => $this->date($r[$c['Date']]),
                'stage' => trim($r[$c['Stage Code']]),
                'detail' => trim((string) $r[$c['Stage Detail']]),
                'account' => (string) (int) $r[$c['Account Code']],
                'head' => trim((string) $r[$c['Account Head']]),
                'description' => trim((string) $r[$c['Description']]),
                'qty' => $r[$c['Qty']] === '' || $r[$c['Qty']] === null ? null : (float) $r[$c['Qty']],
                'unit' => trim((string) $r[$c['Unit']]),
                'rate' => $r[$c['Unit Rate']] === '' || $r[$c['Unit Rate']] === null ? null : (float) $r[$c['Unit Rate']],
                'amount' => (float) $r[$c['Total Amount']],
                'type' => trim((string) $r[$c['Txn Type']]),
                'rework' => strcasecmp(trim((string) $r[$c['Rework']]), 'Yes') === 0,
                'completeness' => trim((string) $r[$c['Completeness']]),
                'remarks' => trim((string) ($r[$c['Remarks']] ?? '')),
            ])
            ->values();
    }

    private function date(mixed $value): string
    {
        return $value instanceof DateTimeInterface ? Carbon::instance($value)->toDateString() : Carbon::parse((string) $value)->toDateString();
    }

    // ---------------------------------------------------------------- reconciliation

    private function reconcile(CropSeason $season, Collection $register, array $book): bool
    {
        $db = DB::table('v_expense_status as v')
            ->join('expenses as e', 'e.id', '=', 'v.expense_id')
            ->join('season_stages as ss', 'ss.id', '=', 'v.season_stage_id')
            ->join('stage_templates as t', 't.id', '=', 'ss.stage_template_id')
            ->join('expense_categories as c', 'c.id', '=', 'v.category_id')
            ->where('v.season_id', $season->id)
            ->select('e.description', 'e.expense_date', 'e.quantity', 'e.unit_price', 'v.amount', 't.code as stage', 'c.id as category_id')
            ->get()
            ->map(fn ($e) => (array) $e + ['txn' => Str::before($e->description, ' – ')])
            ->keyBy('txn');

        $ok = true;
        $tolerance = 0.006; // amounts are stored to 2 decimals; the workbook carries up to 4

        // 1. Line by line
        $categories = $this->categoryIds();
        $problems = [];
        foreach ($register as $row) {
            $e = $db[$row['txn']] ?? null;
            if (! $e) {
                $problems[] = [$row['txn'], 'MISSING in software', '', ''];

                continue;
            }
            $checks = [
                'amount' => abs((float) $e['amount'] - $row['amount']) <= $tolerance,
                'date' => $e['expense_date'] === $row['date'],
                'stage' => $e['stage'] === $this->stageFor($row),
                'category' => (int) $e['category_id'] === (int) ($categories[$row['account']] ?? 0),
                'qty' => $row['qty'] === null ? $e['quantity'] === null : abs((float) $e['quantity'] - $row['qty']) < 0.0005,
                'rate' => $row['rate'] === null ? $e['unit_price'] === null : abs((float) $e['unit_price'] - $row['rate']) < 0.005,
            ];
            foreach (array_keys(array_filter($checks, fn ($v) => ! $v)) as $failed) {
                $problems[] = [$row['txn'], "{$failed} mismatch", json_encode($e), json_encode($row)];
            }
        }
        $extra = $db->keys()->diff($register->pluck('txn'));
        foreach ($extra as $txn) {
            $problems[] = [$txn, 'EXTRA in software (not in workbook)', '', ''];
        }

        $this->newLine();
        $this->info("Line check: {$register->count()} workbook lines vs {$db->count()} software expenses — ".($problems ? count($problems).' problem(s)' : 'all match'));
        if ($problems) {
            $ok = false;
            $this->table(['Txn', 'Issue', 'Software', 'Workbook'], $problems);
        }

        // 2. Stage totals vs Stage_Analysis
        $sheet = $this->labelled($book['Stage_Analysis'], 0, 2, 3, 7);
        $rows = [];
        foreach (['ST01', 'ST02', 'ST03', 'ST04'] as $st) {
            $codes = $st === 'ST04' ? ['HARVEST', 'SILAGE'] : [self::STAGES[$st]];
            $soft = $db->whereIn('stage', $codes)->sum(fn ($e) => (float) $e['amount']);
            $rows[] = $this->row("{$st} → ".implode(' + ', $codes), $sheet[$st] ?? null, $soft, $db->whereIn('stage', $codes)->count(), $register->where('stage', $st)->count(), $ok);
        }
        $rows[] = $this->row('TOTAL', $sheet['TOTAL'] ?? null, $db->sum(fn ($e) => (float) $e['amount']), $db->count(), $register->count(), $ok);
        $this->info('Stage totals (workbook Stage_Analysis vs software)');
        $this->table(['Stage', 'Workbook', 'Software', 'Diff', 'Lines (wb / sw)', 'OK'], $rows);

        // 3. Account heads vs Account_Analysis
        $sheet = $this->labelled($book['Account_Analysis'], 0, 1, 3, 16);
        $rows = [];
        foreach ($register->groupBy('head') as $head => $lines) {
            $ids = collect($lines)->pluck('account')->unique()->map(fn ($a) => $categories[$a] ?? null);
            $soft = $db->whereIn('category_id', $ids)->sum(fn ($e) => (float) $e['amount']);
            $rows[] = $this->row($head.' → '.self::ACCOUNTS[$lines->first()['account']][1], $sheet[$head] ?? null, $soft, $db->whereIn('category_id', $ids)->count(), $lines->count(), $ok);
        }
        $this->info('Account heads (workbook Account_Analysis vs software categories)');
        $this->table(['Account head → category', 'Workbook', 'Software', 'Diff', 'Lines (wb / sw)', 'OK'], $rows);

        // 4. Months vs Monthly_Analysis
        $sheet = $this->labelled($book['Monthly_Analysis'], 0, 1, 3, 8);
        $rows = [];
        foreach ($db->groupBy(fn ($e) => Carbon::parse($e['expense_date'])->format('M-y')) as $month => $lines) {
            $rows[] = $this->row($month, $sheet[$month] ?? null, $lines->sum(fn ($e) => (float) $e['amount']), $lines->count(), $register->filter(fn ($r) => Carbon::parse($r['date'])->format('M-y') === $month)->count(), $ok);
        }
        $this->info('Monthly spend (workbook Monthly_Analysis vs software)');
        $this->table(['Month', 'Workbook', 'Software', 'Diff', 'Lines (wb / sw)', 'OK'], $rows);

        // 5. Headline KPIs vs Dashboard / Silage_Unit_Cost, using the software's own season summary view
        $summary = DB::table('v_season_summary')->where('season_id', $season->id)->first();
        $dash = $book['Dashboard'];
        $kg = (float) $summary->harvested_kg;
        $rows = [
            $this->row('Total crop cost', (float) $dash[5][0], (float) $summary->total_cost, null, null, $ok),
            $this->row('Area (acres)', 14, (float) $summary->total_area_acres, null, null, $ok),
            $this->row('Cost / acre', (float) $dash[5][2], (float) $summary->cost_per_acre, null, null, $ok),
            $this->row('Production kg (provisional)', 280000, $kg, null, null, $ok),
            $this->row('Cost / tonne (prov.)', (float) $dash[5][8], (float) $summary->cost_per_tonne_harvested, null, null, $ok),
            $this->row('Cost / kg (prov.)', (float) $dash[5][4], $kg > 0 ? (float) $summary->total_cost / $kg : 0, null, null, $ok),
            $this->row('Cost / maund (prov.)', (float) $dash[5][6], $kg > 0 ? (float) $summary->total_cost / $kg * 40 : 0, null, null, $ok),
        ];
        $this->info('Headline figures (workbook Dashboard vs software v_season_summary)');
        $this->table(['Measure', 'Workbook', 'Software', 'Diff', '', 'OK'], $rows);

        $this->newLine();
        $ok ? $this->info('RECONCILED: every workbook line is in the software and all reports agree (within 2-decimal rounding).')
            : $this->error('NOT RECONCILED: see the rows marked ✗ above.');

        return $ok;
    }

    private function row(string $label, ?float $workbook, float $software, ?int $swLines, ?int $wbLines, bool &$ok): array
    {
        $diff = $workbook === null ? null : $software - $workbook;
        // Totals may drift by the sum of per-line 2-decimal roundings (max 0.005 × lines); ratios by proportionally less.
        $limit = max(0.05, abs((float) $workbook) * 1e-7);
        $pass = $diff !== null && abs($diff) <= $limit && $swLines === $wbLines;
        $ok = $ok && $pass;

        return [
            $label,
            $workbook === null ? '—' : number_format($workbook, 4),
            number_format($software, 4),
            $diff === null ? 'n/a' : number_format($diff, 4),
            $wbLines === null ? '' : "{$wbLines} / {$swLines}",
            $pass ? '✓' : '✗',
        ];
    }

    /** @return array<string, float> label => value for a block of rows in an analysis sheet */
    private function labelled(array $rows, int $labelCol, int $valueCol, int $from, int $to): array
    {
        $out = [];
        for ($i = $from; $i <= $to && isset($rows[$i]); $i++) {
            $label = trim((string) ($rows[$i][$labelCol] ?? ''));
            if ($label !== '' && is_numeric($rows[$i][$valueCol] ?? null)) {
                $out[$label] = (float) $rows[$i][$valueCol];
            }
        }

        return $out;
    }

    private function categoryIds(): array
    {
        $map = [];
        foreach (self::ACCOUNTS as $account => [$parentName, $childName]) {
            $map[$account] = ExpenseCategory::query()->where('name', $childName)
                ->whereHas('parent', fn ($q) => $q->where('name', $parentName))->value('id');
        }

        return $map;
    }
}
