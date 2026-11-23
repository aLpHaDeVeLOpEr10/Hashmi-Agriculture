<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Align stage names with the "Production Stage" column of the HAL cost register. */
return new class extends Migration
{
    private const NAMES = [
        'LAND_PREP' => ['Land Preparation', 'Crop Land Preparation'],
        'SEED_MAT' => ['Seed & Material Purchase', 'Crop Planting'],
        'GROWING' => ['Crop Growing & Maintenance', 'Crop Care & Maintenance'],
        'HARVEST' => ['Harvesting & Silage', 'Crop Harvesting & Production'],
    ];

    public function up(): void
    {
        foreach (self::NAMES as $code => [, $new]) {
            DB::table('stage_templates')->where('code', $code)->update(['name' => $new]);
        }
    }

    public function down(): void
    {
        foreach (self::NAMES as $code => [$old]) {
            DB::table('stage_templates')->where('code', $code)->update(['name' => $old]);
        }
    }
};
