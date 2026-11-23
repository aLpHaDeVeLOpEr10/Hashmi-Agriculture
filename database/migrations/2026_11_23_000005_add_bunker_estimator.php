<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Bunker estimator from the HAL workbook (Silage_Unit_Cost, sections B, E and F):
 *  - per season: benchmark yield (maund/acre), packing density (kg/m³) and dry-matter %;
 *  - per silage batch: bunker length × width × average settled height (ft), from which
 *    fresh silage kg = L × W × H × 0.0283168 m³/ft³ × density.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crop_seasons', function (Blueprint $table) {
            $table->decimal('benchmark_yield_maund', 8, 2)->nullable()->after('total_area_acres');
            $table->decimal('packing_density_kg_m3', 8, 2)->default(650)->after('benchmark_yield_maund');
            $table->decimal('dry_matter_pct', 5, 2)->default(30)->after('packing_density_kg_m3');
        });

        Schema::table('silage_batches', function (Blueprint $table) {
            $table->decimal('length_ft', 8, 2)->nullable()->after('method');
            $table->decimal('width_ft', 8, 2)->nullable()->after('length_ft');
            $table->decimal('height_ft', 8, 2)->nullable()->after('width_ft');
            $table->decimal('density_kg_m3', 8, 2)->nullable()->after('height_ft');
        });

        // Values from the workbook for the HAL maize season: 500 maund/acre benchmark, 650 kg/m³, 30 % DM.
        DB::table('crop_seasons')->where('code', 'CS-2026-06')->update([
            'benchmark_yield_maund' => 500,
            'packing_density_kg_m3' => 650,
            'dry_matter_pct' => 30,
        ]);
    }

    public function down(): void
    {
        Schema::table('silage_batches', function (Blueprint $table) {
            $table->dropColumn(['length_ft', 'width_ft', 'height_ft', 'density_kg_m3']);
        });
        Schema::table('crop_seasons', function (Blueprint $table) {
            $table->dropColumn(['benchmark_yield_maund', 'packing_density_kg_m3', 'dry_matter_pct']);
        });
    }
};
