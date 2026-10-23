<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crops', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60)->unique();
            $table->boolean('is_active')->default(true);
            $table->auditColumns();
        });

        Schema::create('fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->string('name', 80);
            $table->decimal('area_acres', 8, 2);
            $table->boolean('is_active')->default(true);
            $table->auditColumns();
        });

        Schema::create('crop_seasons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->string('code', 20);
            $table->string('name', 80);
            $table->foreignId('crop_id')->constrained()->restrictOnDelete();
            $table->date('start_date');
            $table->date('expected_end_date')->nullable();
            $table->date('closed_at')->nullable();
            $table->enum('status', ['planned', 'active', 'harvested', 'ensiled', 'closed'])->default('planned');
            $table->decimal('total_area_acres', 8, 2)->default(0);
            $table->text('notes')->nullable();
            $table->auditColumns();
            $table->unique(['farm_id', 'code']);
            $table->index(['farm_id', 'status']);
        });

        Schema::create('season_fields', function (Blueprint $table) {
            $table->foreignId('season_id')->constrained('crop_seasons')->cascadeOnDelete();
            $table->foreignId('field_id')->constrained()->restrictOnDelete();
            $table->decimal('area_acres', 8, 2);
            $table->auditColumns();
            $table->primary(['season_id', 'field_id']);
        });

        Schema::create('stage_templates', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name', 60);
            $table->smallInteger('sequence');
            $table->boolean('is_system')->default(false);
            $table->boolean('is_active')->default(true);
            $table->auditColumns();
        });

        Schema::create('season_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->constrained('crop_seasons')->cascadeOnDelete();
            $table->foreignId('stage_template_id')->constrained()->restrictOnDelete();
            $table->enum('status', ['not_started', 'in_progress', 'completed'])->default('not_started');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('notes')->nullable();
            $table->auditColumns();
            $table->unique(['season_id', 'stage_template_id']);
            $table->unique(['id', 'season_id']);
        });

        Schema::create('resource_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resource_id')->constrained()->cascadeOnDelete();
            $table->foreignId('season_stage_id')->constrained()->cascadeOnDelete();
            $table->date('usage_date');
            $table->decimal('hours', 6, 2)->default(0);
            $table->decimal('acres', 8, 2)->nullable();
            $table->decimal('fuel_litres', 8, 2)->nullable();
            $table->string('operator', 80)->nullable();
            $table->auditColumns();
            $table->index(['resource_id', 'season_stage_id']);
        });

        Schema::create('shared_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('expense_categories')->restrictOnDelete();
            $table->foreignId('resource_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained()->nullOnDelete();
            $table->date('cost_date');
            $table->string('description', 200);
            $table->decimal('total_amount', 14, 2);
            $table->enum('basis', ['hours', 'acres', 'equal', 'manual'])->default('hours');
            $table->enum('status', ['draft', 'allocated'])->default('draft');
            $table->auditColumns();
        });

        Schema::create('recurring_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_stage_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('expense_categories')->restrictOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained()->nullOnDelete();
            $table->string('description', 200);
            $table->decimal('amount', 14, 2);
            $table->enum('frequency', ['weekly', 'monthly']);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->date('next_run_date');
            $table->boolean('is_active')->default(true);
            $table->auditColumns();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->foreignId('season_id')->constrained('crop_seasons')->cascadeOnDelete();
            $table->unsignedBigInteger('season_stage_id');
            $table->foreignId('category_id')->constrained('expense_categories')->restrictOnDelete();
            $table->date('expense_date');
            $table->string('description', 200);
            $table->decimal('quantity', 12, 3)->nullable();
            $table->string('unit_code', 10)->nullable();
            $table->decimal('unit_price', 12, 2)->nullable();
            $table->decimal('amount', 14, 2);
            $table->foreignId('vendor_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('resource_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('field_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('shared_cost_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('recurring_rule_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_notional')->default(false);
            $table->timestampTz('deleted_at')->nullable();
            $table->auditColumns();

            $table->foreign('unit_code')->references('code')->on('units')->nullOnDelete();
            $table->foreign(['season_stage_id', 'season_id'])->references(['id', 'season_id'])->on('season_stages')->cascadeOnDelete();
            $table->index(['season_id', 'season_stage_id']);
            $table->index('category_id');
            $table->index('expense_date');
        });

        Schema::create('payment_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('expense_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 14, 2);
            $table->auditColumns();
            $table->index('expense_id');
        });
    }

    public function down(): void
    {
        foreach (['payment_allocations', 'expenses', 'recurring_rules', 'shared_costs', 'resource_usage', 'season_stages', 'stage_templates', 'season_fields', 'crop_seasons', 'fields', 'crops'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
