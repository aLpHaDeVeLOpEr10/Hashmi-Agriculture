<?php

namespace Database\Seeders;

use App\Models\Crop;
use App\Models\ExpenseCategory;
use App\Models\StageTemplate;
use App\Models\Unit;
use App\Services\MachineService;
use Illuminate\Database\Seeder;

class ReferenceDataSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['litre', 'Litre', 'volume'], ['kg', 'Kilogram', 'weight'], ['tonne', 'Tonne', 'weight'], ['bag', 'Bag', 'count'],
            ['hour', 'Hour', 'time'], ['day', 'Day', 'time'], ['month', 'Month', 'time'], ['acre', 'Acre', 'area'],
            ['trolley', 'Trolley', 'trip'], ['trip', 'Trip', 'trip'], ['unit', 'Unit', 'count'], ['roll', 'Roll', 'count'],
        ] as [$code, $name, $kind]) {
            Unit::updateOrCreate(['code' => $code], ['name' => $name, 'kind' => $kind]);
        }

        foreach ([
            ['LAND_PREP', 'Land Preparation', 1],
            ['SEED_MAT', 'Seed & Material Purchase', 2],
            ['GROWING', 'Crop Growing & Maintenance', 3],
            ['HARVEST', 'Harvesting', 4],
            ['SILAGE', 'Silage Production', 5],
        ] as [$code, $name, $sequence]) {
            StageTemplate::updateOrCreate(['code' => $code], ['name' => $name, 'sequence' => $sequence, 'is_system' => true, 'is_active' => true]);
        }

        foreach (['Maize', 'Sorghum', 'Oats', 'Millet'] as $crop) {
            Crop::firstOrCreate(['name' => $crop]);
        }

        $tree = [
            ['Fuel', 'fuel', 'litre', ['Diesel' => 'litre', 'Petrol' => 'litre', 'Engine oil' => 'litre']],
            ['Labour', 'labour', 'day', ['Daily wages' => 'day', 'Contract labour' => 'acre', 'Permanent staff' => 'month', 'Compaction labour' => 'day']],
            ['Machinery hire', 'machinery', 'hour', ['Tractor rent' => 'hour', 'Harvester / chopper rent' => 'acre', 'Rotavator & disc' => 'acre', 'Planter' => 'acre']],
            ['Maintenance', 'maintenance', 'unit', ['Tractor repair' => 'unit', 'Tyres & battery' => 'unit', 'Servicing' => 'unit']],
            ['Seed', 'material', 'bag', ['Hybrid maize seed' => 'bag', 'Sorghum seed' => 'kg', 'Oats seed' => 'kg']],
            ['Fertilizer', 'material', 'bag', ['Urea' => 'bag', 'DAP' => 'bag', 'Potash (SOP)' => 'bag', 'Farmyard manure' => 'trolley']],
            ['Crop protection', 'material', 'litre', ['Herbicide' => 'litre', 'Insecticide' => 'litre', 'Fungicide' => 'litre']],
            ['Silage materials', 'material', 'unit', ['Plastic sheet' => 'roll', 'Silage additive / inoculant' => 'kg', 'Tyres & weights' => 'unit']],
            ['Irrigation', 'utility', 'month', ['Tubewell electricity' => 'month', 'Canal water (abiana)' => 'acre']],
            ['Transport', 'transport', 'trip', ['Trolley hire' => 'trip', 'Loading & unloading' => 'trip']],
            ['Services', 'service', 'unit', ['Soil testing' => 'unit', 'Agronomist visit' => 'unit']],
            ['Other', 'other', null, ['Miscellaneous' => null]],
        ];

        foreach ($tree as [$name, $type, $unit, $children]) {
            $parent = ExpenseCategory::query()->firstOrCreate(
                ['farm_id' => null, 'parent_id' => null, 'name' => $name],
                ['cost_type' => $type, 'default_unit' => $unit, 'is_active' => true],
            );
            foreach ($children as $child => $childUnit) {
                ExpenseCategory::query()->firstOrCreate(
                    ['farm_id' => null, 'parent_id' => $parent->id, 'name' => $child],
                    ['cost_type' => $type, 'default_unit' => $childUnit, 'is_active' => true],
                );
            }
        }

        MachineService::notionalCategory();
    }
}
