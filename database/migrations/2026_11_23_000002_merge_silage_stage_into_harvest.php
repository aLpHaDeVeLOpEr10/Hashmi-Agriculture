<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Harvesting and silage-making are one operation on the farm, so the SILAGE stage is folded into
 * HARVEST ("Harvesting & Silage"): every season's silage lines, recurring rules and machine usage
 * move to its harvest stage, the stage dates/status are combined, and the SILAGE template is removed.
 */
return new class extends Migration
{
    public function up(): void
    {
        $harvest = DB::table('stage_templates')->where('code', 'HARVEST')->value('id');
        $silage = DB::table('stage_templates')->where('code', 'SILAGE')->value('id');

        if ($harvest) {
            DB::table('stage_templates')->where('id', $harvest)->update(['name' => 'Harvesting & Silage']);
        }
        if (! $harvest || ! $silage) {
            return;
        }

        DB::transaction(function () use ($harvest, $silage) {
            foreach (DB::table('season_stages')->where('stage_template_id', $silage)->get() as $from) {
                $into = DB::table('season_stages')->where('season_id', $from->season_id)->where('stage_template_id', $harvest)->first();

                if (! $into) {
                    DB::table('season_stages')->where('id', $from->id)->update(['stage_template_id' => $harvest]);

                    continue;
                }

                foreach (['expenses', 'recurring_rules', 'resource_usage'] as $table) {
                    DB::table($table)->where('season_stage_id', $from->id)->update(['season_stage_id' => $into->id]);
                }

                DB::table('season_stages')->where('id', $into->id)->update([
                    'start_date' => collect([$into->start_date, $from->start_date])->filter()->min(),
                    'end_date' => $into->status === 'completed' && $from->status === 'completed'
                        ? collect([$into->end_date, $from->end_date])->filter()->max()
                        : null,
                    'status' => $this->combinedStatus($into->status, $from->status),
                    'notes' => collect([$into->notes, $from->notes])->filter()->implode("\n") ?: null,
                ]);

                DB::table('season_stages')->where('id', $from->id)->delete();
            }

            $categories = DB::table('expense_category_stage')->where('stage_template_id', $silage)->pluck('expense_category_id');
            foreach ($categories as $categoryId) {
                DB::table('expense_category_stage')->insertOrIgnore(['expense_category_id' => $categoryId, 'stage_template_id' => $harvest]);
            }
            DB::table('expense_category_stage')->where('stage_template_id', $silage)->delete();

            DB::table('stage_templates')->where('id', $silage)->delete();
        });
    }

    /** Irreversible: the separate silage stage no longer exists. Restore from storage/backups if needed. */
    public function down(): void {}

    private function combinedStatus(string $a, string $b): string
    {
        if ($a === 'completed' && $b === 'completed') {
            return 'completed';
        }

        return $a === 'not_started' && $b === 'not_started' ? 'not_started' : 'in_progress';
    }
};
