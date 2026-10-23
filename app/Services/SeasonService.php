<?php

namespace App\Services;

use App\Models\CropSeason;
use App\Models\SeasonStage;
use App\Models\StageTemplate;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SeasonService
{
    /**
     * @param  array{name: string, crop_id: int, start_date: string, expected_end_date?: ?string, notes?: ?string, fields: array<int, float|string>}  $data
     */
    public function create(array $data): CropSeason
    {
        return DB::transaction(function () use ($data) {
            $season = new CropSeason([
                'name' => $data['name'],
                'crop_id' => $data['crop_id'],
                'start_date' => $data['start_date'],
                'expected_end_date' => $data['expected_end_date'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => 'planned',
                'code' => $this->nextCode(Carbon::parse($data['start_date'])),
                'total_area_acres' => array_sum(array_map('floatval', $data['fields'])),
            ]);
            $season->save();

            $season->fields()->sync($this->pivot($data['fields']));

            StageTemplate::query()->where('is_system', true)->where('is_active', true)->orderBy('sequence')->get()
                ->each(fn (StageTemplate $template) => $season->stages()->create(['stage_template_id' => $template->id]));

            return $season;
        });
    }

    public function update(CropSeason $season, array $data): CropSeason
    {
        $this->ensureOpen($season);

        return DB::transaction(function () use ($season, $data) {
            $season->update([
                'name' => $data['name'],
                'crop_id' => $data['crop_id'],
                'start_date' => $data['start_date'],
                'expected_end_date' => $data['expected_end_date'] ?? null,
                'notes' => $data['notes'] ?? null,
                'total_area_acres' => array_sum(array_map('floatval', $data['fields'])),
            ]);
            $season->fields()->sync($this->pivot($data['fields']));

            return $season;
        });
    }

    public function nextCode(Carbon $date): string
    {
        $base = 'CS-'.$date->format('Y-m');
        $existing = CropSeason::query()->where('code', 'like', $base.'%')->pluck('code');

        if (! $existing->contains($base)) {
            return $base;
        }

        $n = 2;
        while ($existing->contains("{$base}-{$n}")) {
            $n++;
        }

        return "{$base}-{$n}";
    }

    public function setStatus(CropSeason $season, string $status): void
    {
        $this->ensureOpen($season);

        if (! in_array($status, ['planned', 'active', 'harvested', 'ensiled'], true)) {
            throw ValidationException::withMessages(['status' => __('Use “Close season” to close a season.')]);
        }

        $season->update(['status' => $status]);
    }

    public function addCustomStage(CropSeason $season, string $name): SeasonStage
    {
        $this->ensureOpen($season);

        return DB::transaction(function () use ($season, $name) {
            $template = StageTemplate::create([
                'code' => 'C-'.Str::upper(Str::random(8)),
                'name' => $name,
                'sequence' => (int) StageTemplate::max('sequence') + 1,
                'is_system' => false,
            ]);

            return $season->stages()->create(['stage_template_id' => $template->id]);
        });
    }

    public function setStageStatus(SeasonStage $stage, string $status): void
    {
        $this->ensureOpen($stage->season);

        $stage->status = $status;
        if ($status !== 'not_started') {
            $stage->start_date ??= today();
        }
        $stage->end_date = $status === 'completed' ? ($stage->end_date ?? today()) : null;
        $stage->save();

        if ($status !== 'not_started' && $stage->season->status === 'planned') {
            $stage->season->update(['status' => 'active']);
        }
    }

    public function close(CropSeason $season): void
    {
        $this->ensureOpen($season);

        DB::transaction(function () use ($season) {
            $season->update(['status' => 'closed', 'closed_at' => today()]);
            $this->freezeSilageCost($season);
        });
    }

    public function reopen(CropSeason $season): void
    {
        if (! $season->isClosed()) {
            return;
        }

        DB::transaction(function () use ($season) {
            $status = match (true) {
                $season->batches()->exists() => 'ensiled',
                $season->harvests()->exists() => 'harvested',
                default => 'active',
            };
            $season->update(['status' => $status, 'closed_at' => null]);
            $season->batches()->get()->each(fn ($batch) => $batch->update(['cost_per_kg' => null]));
        });
    }

    private function freezeSilageCost(CropSeason $season): void
    {
        $costPerKg = $season->summary()->value('silage_cost_per_kg');

        $season->batches()->get()->each(fn ($batch) => $batch->update([
            'cost_per_kg' => $costPerKg !== null ? round((float) $costPerKg, 4) : null,
        ]));
    }

    public function ensureOpen(CropSeason $season): void
    {
        if ($season->isClosed()) {
            throw ValidationException::withMessages(['season' => __('This season is closed. A manager or owner must reopen it before changes can be made.')]);
        }
    }

    private function pivot(array $fields): array
    {
        return collect($fields)->mapWithKeys(fn ($area, $fieldId) => [(int) $fieldId => ['area_acres' => $area]])->all();
    }
}
