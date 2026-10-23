<?php

namespace Database\Seeders;

use App\Models\Crop;
use App\Models\ExpenseCategory;
use App\Models\Farm;
use App\Models\Field;
use App\Models\Machine;
use App\Models\MachineUsage;
use App\Models\RecurringRule;
use App\Models\SeasonStage;
use App\Models\SharedCost;
use App\Models\StorageUnit;
use App\Models\User;
use App\Models\Vendor;
use App\Services\ExpenseService;
use App\Services\PaymentService;
use App\Services\ProductionService;
use App\Services\SeasonService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;

class DemoFarmSeeder extends Seeder
{
    public function run(SeasonService $seasons, ExpenseService $expenses, PaymentService $payments, ProductionService $production): void
    {
        $farm = Farm::create(['name' => 'Hashmi Agriculture Farm', 'location' => 'Renala Khurd, District Okara']);

        $owner = new User(['name' => 'Imran Hashmi', 'phone' => '03001234567', 'role' => 'owner', 'password' => 'password']);
        $owner->farm_id = $farm->id;
        $owner->save();
        Auth::setUser($owner);

        foreach ([
            ['Tariq Mehmood', '03011234567', 'manager'],
            ['Usman Ghani', '03021234567', 'data_entry'],
            ['Ayesha Siddiqui', '03031234567', 'viewer'],
        ] as [$name, $phone, $role]) {
            $user = new User(['name' => $name, 'phone' => $phone, 'role' => $role, 'password' => 'password']);
            $user->farm_id = $farm->id;
            $user->save();
        }

        $fields = collect([
            ['North Block A-1', 10], ['North Block A-2', 12], ['Tubewell Square B-1', 8], ['River Bank R-1', 15],
        ])->mapWithKeys(fn ($f) => [$f[0] => Field::create(['name' => $f[0], 'area_acres' => $f[1]])]);

        $vendor = collect([
            ['Kissan Seed Centre', '03214567890', 'supplier'],
            ['PSO Renala Filling Station', '0442630011', 'supplier'],
            ['Rana Tractor Services', '03334567812', 'rental'],
            ['Ghulam Rasool (labour contractor)', '03004561230', 'labour'],
            ['LESCO', null, 'utility'],
            ['Okara Agri Traders', '03124567711', 'supplier'],
        ])->mapWithKeys(fn ($v) => [$v[0] => Vendor::create(['name' => $v[0], 'phone' => $v[1], 'type' => $v[2]])]);

        $tractor = Machine::create(['name' => 'Massey Ferguson 385', 'type' => 'tractor', 'ownership' => 'owned', 'internal_rate_per_hour' => 1500]);
        Machine::create(['name' => 'Rented forage chopper', 'type' => 'chopper', 'ownership' => 'rented']);
        Machine::create(['name' => 'Tubewell pump B-1', 'type' => 'pump', 'ownership' => 'owned']);

        $cat = fn (string $name) => ExpenseCategory::query()->where('name', $name)->whereNotNull('parent_id')->firstOrFail()->id;
        $maize = Crop::where('name', 'Maize')->value('id');
        $oats = Crop::where('name', 'Oats')->value('id');

        $add = function ($season, string $stageCode, array $lines) use ($expenses) {
            $stage = SeasonStage::query()->where('season_id', $season->id)
                ->whereHas('template', fn ($q) => $q->where('code', $stageCode))->firstOrFail();
            foreach ($lines as $line) {
                $expenses->save($line + ['season_stage_id' => $stage->id]);
            }

            return $stage;
        };

        $spring = $seasons->create([
            'name' => 'March 2026', 'crop_id' => $maize, 'start_date' => '2026-03-02', 'expected_end_date' => '2026-06-30',
            'fields' => [$fields['North Block A-1']->id => 10],
            'notes' => 'Spring maize for silage, hybrid DK-6789.',
        ]);
        $plan = [
            'LAND_PREP' => [
                ['expense_date' => '2026-03-02', 'category_id' => $cat('Diesel'), 'description' => 'Diesel for ploughing and discing', 'quantity' => 100, 'unit_code' => 'litre', 'unit_price' => 250, 'vendor_id' => $vendor['PSO Renala Filling Station']->id, 'resource_id' => $tractor->id],
                ['expense_date' => '2026-03-03', 'category_id' => $cat('Rotavator & disc'), 'description' => 'Rotavator, 10 acres', 'quantity' => 10, 'unit_code' => 'acre', 'unit_price' => 1000, 'vendor_id' => $vendor['Rana Tractor Services']->id],
                ['expense_date' => '2026-03-04', 'category_id' => $cat('Daily wages'), 'description' => 'Levelling and ridging labour', 'quantity' => 5, 'unit_code' => 'day', 'unit_price' => 1000],
            ],
            'SEED_MAT' => [
                ['expense_date' => '2026-03-06', 'category_id' => $cat('Hybrid maize seed'), 'description' => 'DK-6789 hybrid seed, 10 bags', 'quantity' => 10, 'unit_code' => 'bag', 'unit_price' => 4000, 'vendor_id' => $vendor['Kissan Seed Centre']->id],
                ['expense_date' => '2026-03-06', 'category_id' => $cat('DAP'), 'description' => 'DAP at sowing', 'quantity' => 1.25, 'unit_code' => 'bag', 'unit_price' => 12000, 'vendor_id' => $vendor['Okara Agri Traders']->id],
            ],
            'GROWING' => [
                ['expense_date' => '2026-04-30', 'category_id' => $cat('Tubewell electricity'), 'description' => 'Tubewell electricity, Mar–Apr', 'amount' => 20000, 'vendor_id' => $vendor['LESCO']->id],
                ['expense_date' => '2026-04-12', 'category_id' => $cat('Urea'), 'description' => 'Urea top dressing', 'quantity' => 4, 'unit_code' => 'bag', 'unit_price' => 4500, 'vendor_id' => $vendor['Okara Agri Traders']->id],
            ],
            'HARVEST' => [
                ['expense_date' => '2026-06-14', 'category_id' => $cat('Harvester / chopper rent'), 'description' => 'Forage chopper hire, 10 acres', 'quantity' => 10, 'unit_code' => 'acre', 'unit_price' => 3000, 'vendor_id' => $vendor['Rana Tractor Services']->id],
                ['expense_date' => '2026-06-14', 'category_id' => $cat('Trolley hire'), 'description' => 'Trolleys to silage pit', 'quantity' => 24, 'unit_code' => 'trip', 'unit_price' => 500],
            ],
            'SILAGE' => [
                ['expense_date' => '2026-06-15', 'category_id' => $cat('Plastic sheet'), 'description' => 'Plastic sheet and inoculant', 'amount' => 25000, 'vendor_id' => $vendor['Okara Agri Traders']->id],
                ['expense_date' => '2026-06-16', 'category_id' => $cat('Compaction labour'), 'description' => 'Compaction and sealing labour', 'quantity' => 20, 'unit_code' => 'day', 'unit_price' => 1000, 'vendor_id' => $vendor['Ghulam Rasool (labour contractor)']->id],
            ],
        ];
        foreach ($plan as $code => $lines) {
            $add($spring, $code, $lines)->update(['status' => 'completed', 'end_date' => end($lines)['expense_date']]);
        }
        $spring->update(['status' => 'ensiled']);

        $autumn = $seasons->create([
            'name' => 'August 2026', 'crop_id' => $maize, 'start_date' => '2026-08-03', 'expected_end_date' => '2026-11-15',
            'fields' => [$fields['North Block A-2']->id => 12, $fields['River Bank R-1']->id => 15],
            'notes' => 'Autumn maize across two fields.',
        ]);
        $add($autumn, 'LAND_PREP', [
            ['expense_date' => '2026-08-03', 'category_id' => $cat('Diesel'), 'description' => 'Diesel for ploughing, 27 acres', 'quantity' => 260, 'unit_code' => 'litre', 'unit_price' => 262, 'vendor_id' => $vendor['PSO Renala Filling Station']->id, 'resource_id' => $tractor->id],
            ['expense_date' => '2026-08-05', 'category_id' => $cat('Rotavator & disc'), 'description' => 'Rotavator, 27 acres', 'quantity' => 27, 'unit_code' => 'acre', 'unit_price' => 1100, 'vendor_id' => $vendor['Rana Tractor Services']->id],
        ])->update(['status' => 'completed', 'end_date' => '2026-08-06']);
        $add($autumn, 'SEED_MAT', [
            ['expense_date' => '2026-08-07', 'category_id' => $cat('Hybrid maize seed'), 'description' => 'Pioneer 30Y87, 27 bags', 'quantity' => 27, 'unit_code' => 'bag', 'unit_price' => 4300, 'vendor_id' => $vendor['Kissan Seed Centre']->id],
            ['expense_date' => '2026-08-07', 'category_id' => $cat('DAP'), 'description' => 'DAP at sowing', 'quantity' => 27, 'unit_code' => 'bag', 'unit_price' => 12400, 'vendor_id' => $vendor['Okara Agri Traders']->id],
        ])->update(['status' => 'completed', 'end_date' => '2026-08-08']);
        $add($autumn, 'GROWING', [
            ['expense_date' => '2026-08-31', 'category_id' => $cat('Tubewell electricity'), 'description' => 'Tubewell electricity, August', 'amount' => 31500, 'vendor_id' => $vendor['LESCO']->id],
            ['expense_date' => '2026-09-01', 'category_id' => $cat('Urea'), 'description' => 'Urea, 1st split', 'quantity' => 27, 'unit_code' => 'bag', 'unit_price' => 4650, 'vendor_id' => $vendor['Okara Agri Traders']->id],
            ['expense_date' => '2026-09-30', 'category_id' => $cat('Tubewell electricity'), 'description' => 'Tubewell electricity, September', 'amount' => 29800, 'vendor_id' => $vendor['LESCO']->id],
            ['expense_date' => '2026-09-10', 'category_id' => $cat('Herbicide'), 'description' => 'Atrazine + S-metolachlor', 'quantity' => 27, 'unit_code' => 'litre', 'unit_price' => 1450],
        ])->update(['status' => 'completed', 'end_date' => '2026-10-15']);
        $add($autumn, 'HARVEST', [
            ['expense_date' => '2026-10-21', 'category_id' => $cat('Harvester / chopper rent'), 'description' => 'Forage chopper hire, first 12 acres', 'quantity' => 12, 'unit_code' => 'acre', 'unit_price' => 3200, 'vendor_id' => $vendor['Rana Tractor Services']->id],
        ]);
        $autumn->update(['status' => 'active']);

        $oatsSeason = $seasons->create([
            'name' => 'October 2026', 'crop_id' => $oats, 'start_date' => '2026-10-15', 'expected_end_date' => '2027-01-31',
            'fields' => [$fields['Tubewell Square B-1']->id => 8],
            'notes' => 'Winter oats for fodder and silage.',
        ]);
        $add($oatsSeason, 'LAND_PREP', [
            ['expense_date' => '2026-10-16', 'category_id' => $cat('Diesel'), 'description' => 'Diesel for land preparation', 'quantity' => 70, 'unit_code' => 'litre', 'unit_price' => 265, 'vendor_id' => $vendor['PSO Renala Filling Station']->id, 'resource_id' => $tractor->id],
            ['expense_date' => '2026-10-18', 'category_id' => $cat('Farmyard manure'), 'description' => 'Farmyard manure, 6 trolleys', 'quantity' => 6, 'unit_code' => 'trolley', 'unit_price' => 3500],
        ]);

        $pit = StorageUnit::create(['name' => 'Pit 1', 'type' => 'pit', 'capacity_tonnes' => 200]);
        $bunker = StorageUnit::create(['name' => 'Bunker 2', 'type' => 'bunker', 'capacity_tonnes' => 400]);
        StorageUnit::create(['name' => 'Pit 3', 'type' => 'pit', 'capacity_tonnes' => 150]);

        $production->saveHarvest($spring, ['harvest_date' => '2026-06-14', 'field_id' => $fields['North Block A-1']->id, 'method' => 'weighbridge', 'quantity_kg' => 200000, 'moisture_pct' => 67]);
        $springBatch = $production->saveBatch($spring, ['storage_unit_id' => $pit->id, 'fill_start' => '2026-06-14', 'sealed_date' => '2026-06-17', 'fresh_weight_kg' => 190000, 'method' => 'weighbridge', 'expected_loss_pct' => 10]);
        $production->advanceBatch($springBatch, 'feeding', '2026-08-01');

        foreach ($spring->expenses()->get() as $expense) {
            $payments->payExpense($expense, $expense->amount, $expense->expense_date->addDays(7)->toDateString(), 'cash');
        }
        $seasons->close($spring);
        $spring->update(['closed_at' => '2026-07-05']);

        $production->saveHarvest($autumn, ['harvest_date' => '2026-10-21', 'field_id' => $fields['North Block A-2']->id, 'method' => 'trolley_count', 'trolley_count' => 48, 'avg_trolley_kg' => 4500, 'moisture_pct' => 68]);
        $production->saveBatch($autumn, ['storage_unit_id' => $bunker->id, 'fill_start' => '2026-10-21', 'fresh_weight_kg' => 205000, 'method' => 'trolley_count', 'expected_loss_pct' => 10]);

        $autumnBills = $autumn->expenses()->orderBy('expense_date')->get();
        $payments->record(
            ['vendor_id' => $vendor['Okara Agri Traders']->id, 'payment_date' => '2026-09-05', 'amount' => 400000, 'method' => 'bank', 'reference' => 'HBL-552190'],
            $autumnBills->where('vendor_id', $vendor['Okara Agri Traders']->id)->sortBy('expense_date')->reduce(function ($carry, $bill) {
                $take = min((float) $bill->amount, 400000 - array_sum($carry));

                return $take > 0 ? $carry + [$bill->id => $take] : $carry;
            }, []),
        );
        foreach ($autumnBills->whereIn('vendor_id', [$vendor['PSO Renala Filling Station']->id, $vendor['Rana Tractor Services']->id, $vendor['Kissan Seed Centre']->id]) as $bill) {
            if ($bill->expense_date->lt('2026-10-01')) {
                $payments->payExpense($bill, $bill->amount, $bill->expense_date->addDays(3)->toDateString(), 'cash');
            }
        }

        $stageOf = fn ($season, string $code) => SeasonStage::query()->where('season_id', $season->id)->whereHas('template', fn ($q) => $q->where('code', $code))->firstOrFail();
        foreach ([
            [$autumn, 'LAND_PREP', '2026-08-03', 24, 27, 260, 'Rashid Ali'],
            [$autumn, 'HARVEST', '2026-10-21', 9, 12, 70, 'Rashid Ali'],
            [$oatsSeason, 'LAND_PREP', '2026-10-16', 16, 8, 70, 'Rashid Ali'],
        ] as [$season, $code, $date, $hours, $acres, $litres, $operator]) {
            MachineUsage::create(['resource_id' => $tractor->id, 'season_stage_id' => $stageOf($season, $code)->id, 'usage_date' => $date, 'hours' => $hours, 'acres' => $acres, 'fuel_litres' => $litres, 'operator' => $operator]);
        }

        SharedCost::create([
            'category_id' => $cat('Tractor repair'), 'resource_id' => $tractor->id, 'vendor_id' => $vendor['Rana Tractor Services']->id,
            'cost_date' => '2026-10-22', 'description' => 'MF-385 hydraulic pump repair', 'total_amount' => 12000, 'basis' => 'hours', 'status' => 'draft',
        ]);

        RecurringRule::create([
            'season_stage_id' => $stageOf($oatsSeason, 'GROWING')->id, 'category_id' => $cat('Tubewell electricity'), 'vendor_id' => $vendor['LESCO']->id,
            'description' => 'Tubewell electricity', 'amount' => 14500, 'frequency' => 'monthly',
            'start_date' => '2026-10-20', 'next_run_date' => '2026-10-20', 'end_date' => '2027-01-31', 'is_active' => true,
        ]);
        RecurringRule::create([
            'season_stage_id' => $stageOf($autumn, 'HARVEST')->id, 'category_id' => $cat('Daily wages'), 'vendor_id' => $vendor['Ghulam Rasool (labour contractor)']->id,
            'description' => 'Harvest crew weekly wages', 'amount' => 21000, 'frequency' => 'weekly',
            'start_date' => '2026-10-19', 'next_run_date' => '2026-10-26', 'end_date' => '2026-11-15', 'is_active' => true,
        ]);

        Auth::forgetUser();
    }
}
