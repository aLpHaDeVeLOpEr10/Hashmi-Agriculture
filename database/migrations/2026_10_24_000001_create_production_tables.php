<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('harvest_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->constrained('crop_seasons')->cascadeOnDelete();
            $table->foreignId('field_id')->nullable()->constrained()->nullOnDelete();
            $table->date('harvest_date');
            $table->decimal('quantity_kg', 12, 3);
            $table->enum('method', ['weighbridge', 'trolley_count', 'estimate']);
            $table->integer('trolley_count')->nullable();
            $table->decimal('avg_trolley_kg', 10, 2)->nullable();
            $table->decimal('moisture_pct', 5, 2)->nullable();
            $table->auditColumns();
            $table->index('season_id');
        });

        Schema::create('storage_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->string('name', 60);
            $table->enum('type', ['pit', 'bunker', 'bag', 'bale_stack']);
            $table->decimal('capacity_tonnes', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->auditColumns();
        });

        Schema::create('silage_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->constrained('crop_seasons')->cascadeOnDelete();
            $table->foreignId('storage_unit_id')->constrained()->restrictOnDelete();
            $table->date('fill_start');
            $table->date('sealed_date')->nullable();
            $table->decimal('fresh_weight_kg', 12, 3);
            $table->enum('method', ['weighbridge', 'trolley_count', 'pit_volume']);
            $table->decimal('expected_loss_pct', 5, 2)->default(10);
            $table->date('opened_date')->nullable();
            $table->decimal('cost_per_kg', 10, 4)->nullable();
            $table->enum('status', ['filling', 'sealed', 'feeding', 'finished'])->default('filling');
            $table->auditColumns();
            $table->index('season_id');
        });

        DB::statement('DROP VIEW IF EXISTS v_season_summary');
        DB::statement(<<<'SQL'
            CREATE VIEW v_season_summary AS
            SELECT
                s.id AS season_id,
                s.farm_id,
                s.total_area_acres,
                COALESCE(t.total, 0) AS total_cost,
                COALESCE(t.total, 0) + COALESCE(t.notional_total, 0) AS economic_cost,
                COALESCE(t.paid, 0) AS paid,
                COALESCE(t.balance, 0) AS outstanding,
                CASE WHEN s.total_area_acres > 0 THEN COALESCE(t.total, 0) * 1.0 / s.total_area_acres END AS cost_per_acre,
                COALESCE(h.harvested_kg, 0) AS harvested_kg,
                CASE WHEN s.total_area_acres > 0 THEN COALESCE(h.harvested_kg, 0) * 1.0 / s.total_area_acres END AS yield_kg_per_acre,
                CASE WHEN h.harvested_kg > 0 THEN COALESCE(t.total, 0) * 1000.0 / h.harvested_kg END AS cost_per_tonne_harvested,
                COALESCE(b.fresh_kg, 0) AS silage_fresh_kg,
                COALESCE(b.usable_kg, 0) AS silage_usable_kg,
                CASE WHEN b.usable_kg > 0 THEN COALESCE(t.total, 0) * 1.0 / b.usable_kg END AS silage_cost_per_kg,
                CASE WHEN b.usable_kg > 0 THEN COALESCE(t.total, 0) * 1000.0 / b.usable_kg END AS silage_cost_per_tonne
            FROM crop_seasons s
            LEFT JOIN (
                SELECT season_id, SUM(total) AS total, SUM(notional_total) AS notional_total, SUM(paid) AS paid, SUM(balance) AS balance
                FROM v_stage_totals GROUP BY season_id
            ) t ON t.season_id = s.id
            LEFT JOIN (
                SELECT season_id, SUM(quantity_kg) AS harvested_kg FROM harvest_records GROUP BY season_id
            ) h ON h.season_id = s.id
            LEFT JOIN (
                SELECT season_id, SUM(fresh_weight_kg) AS fresh_kg, SUM(fresh_weight_kg * (100 - expected_loss_pct) / 100.0) AS usable_kg
                FROM silage_batches GROUP BY season_id
            ) b ON b.season_id = s.id
        SQL);
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS v_season_summary');
        DB::statement(<<<'SQL'
            CREATE VIEW v_season_summary AS
            SELECT
                s.id AS season_id,
                s.farm_id,
                s.total_area_acres,
                COALESCE(t.total, 0) AS total_cost,
                COALESCE(t.total, 0) + COALESCE(t.notional_total, 0) AS economic_cost,
                COALESCE(t.paid, 0) AS paid,
                COALESCE(t.balance, 0) AS outstanding,
                CASE WHEN s.total_area_acres > 0 THEN COALESCE(t.total, 0) * 1.0 / s.total_area_acres END AS cost_per_acre
            FROM crop_seasons s
            LEFT JOIN (
                SELECT season_id, SUM(total) AS total, SUM(notional_total) AS notional_total, SUM(paid) AS paid, SUM(balance) AS balance
                FROM v_stage_totals GROUP BY season_id
            ) t ON t.season_id = s.id
        SQL);

        Schema::dropIfExists('silage_batches');
        Schema::dropIfExists('storage_units');
        Schema::dropIfExists('harvest_records');
    }
};
