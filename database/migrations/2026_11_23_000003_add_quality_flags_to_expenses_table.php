<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Data-quality flags carried over from the cost register: how well a line is documented
 * (e.g. a pesticide lump sum with no product breakdown is "incomplete") and whether it was
 * spent redoing work (re-ploughing, re-planting) — the "avoidable re-work" figure.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->enum('completeness', ['complete', 'partial', 'incomplete'])->default('complete')->after('is_notional');
            $table->boolean('is_rework')->default(false)->after('completeness');
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn(['completeness', 'is_rework']);
        });
    }
};
