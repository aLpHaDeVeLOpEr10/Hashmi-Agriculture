<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Links a category to the stages it may be used in. A category with no
     * rows here is available in every stage.
     */
    public function up(): void
    {
        Schema::create('expense_category_stage', function (Blueprint $table) {
            $table->foreignId('expense_category_id')->constrained('expense_categories')->cascadeOnDelete();
            $table->foreignId('stage_template_id')->constrained('stage_templates')->cascadeOnDelete();
            $table->primary(['expense_category_id', 'stage_template_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_category_stage');
    }
};
