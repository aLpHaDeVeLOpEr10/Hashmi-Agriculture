<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('phone', 20)->nullable();
            $table->enum('type', ['supplier', 'contractor', 'labour', 'rental', 'utility', 'other'])->default('supplier');
            $table->boolean('is_active')->default(true);
            $table->auditColumns();
        });

        Schema::create('units', function (Blueprint $table) {
            $table->string('code', 10)->primary();
            $table->string('name', 40);
            $table->enum('kind', ['weight', 'volume', 'time', 'area', 'count', 'trip']);
            $table->auditColumns();
        });

        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('expense_categories')->restrictOnDelete();
            $table->string('name', 80);
            $table->enum('cost_type', ['fuel', 'labour', 'machinery', 'maintenance', 'material', 'service', 'transport', 'utility', 'other']);
            $table->string('default_unit', 10)->nullable();
            $table->foreign('default_unit')->references('code')->on('units')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->auditColumns();
        });

        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->enum('type', ['tractor', 'harvester', 'chopper', 'pump', 'trolley', 'implement', 'vehicle']);
            $table->enum('ownership', ['owned', 'rented'])->default('owned');
            $table->decimal('internal_rate_per_hour', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->auditColumns();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained()->nullOnDelete();
            $table->date('payment_date');
            $table->decimal('amount', 14, 2);
            $table->enum('method', ['cash', 'bank', 'cheque', 'jazzcash', 'easypaisa'])->default('cash');
            $table->string('reference', 60)->nullable();
            $table->auditColumns();
            $table->index(['farm_id', 'payment_date']);
        });

        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->string('owner_table', 40);
            $table->unsignedBigInteger('owner_id');
            $table->string('file_url');
            $table->string('mime_type', 100);
            $table->auditColumns();
            $table->index(['owner_table', 'owner_id']);
        });

        Schema::create('audit_log', function (Blueprint $table) {
            $table->id();
            $table->string('table_name', 60);
            $table->unsignedBigInteger('record_id');
            $table->enum('action', ['insert', 'update', 'delete']);
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('changed_at');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->index(['table_name', 'record_id']);
        });
    }

    public function down(): void
    {
        foreach (['audit_log', 'attachments', 'payments', 'resources', 'expense_categories', 'units', 'vendors'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
