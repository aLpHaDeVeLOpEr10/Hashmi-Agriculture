<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** HAL stores silage in bunkers, not pits: existing pit storage units become bunkers ("Pit 1" → "Bunker 1"). */
return new class extends Migration
{
    public function up(): void
    {
        foreach (DB::table('storage_units')->where('type', 'pit')->get() as $unit) {
            DB::table('storage_units')->where('id', $unit->id)->update([
                'type' => 'bunker',
                'name' => preg_replace('/^Pit\b/i', 'Bunker', $unit->name),
            ]);
        }
    }

    /** Irreversible: which units were pits is not recorded. */
    public function down(): void {}
};
