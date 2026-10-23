<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Machine;
use App\Models\MachineUsage;
use App\Models\SeasonStage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MachineService
{
    public const NOTIONAL_CATEGORY = 'Own machine use (notional)';

    public function __construct(private SeasonService $seasons) {}

    public function logUsage(array $data, bool $addNotional = false, ?MachineUsage $usage = null): MachineUsage
    {
        $stage = SeasonStage::with('season')->findOrFail($data['season_stage_id']);
        $this->seasons->ensureOpen($stage->season);
        $machine = Machine::findOrFail($data['resource_id']);

        return DB::transaction(function () use ($data, $addNotional, $usage, $stage, $machine) {
            $usage ??= new MachineUsage;
            $usage->fill(collect($data)->only(['resource_id', 'season_stage_id', 'usage_date', 'hours', 'acres', 'fuel_litres', 'operator'])->all());
            $usage->save();

            if ($addNotional) {
                if ($machine->ownership !== 'owned' || ! $machine->internal_rate_per_hour) {
                    throw ValidationException::withMessages(['notional' => __('Set an internal rate per hour on this owned machine first.')]);
                }
                Expense::create([
                    'season_id' => $stage->season_id,
                    'season_stage_id' => $stage->id,
                    'category_id' => self::notionalCategory()->id,
                    'expense_date' => $usage->usage_date,
                    'description' => __(':machine, :hours h at internal rate', ['machine' => $machine->name, 'hours' => (float) $usage->hours]),
                    'quantity' => $usage->hours,
                    'unit_code' => 'hour',
                    'unit_price' => $machine->internal_rate_per_hour,
                    'amount' => round((float) $usage->hours * (float) $machine->internal_rate_per_hour, 2),
                    'resource_id' => $machine->id,
                    'is_notional' => true,
                ]);
            }

            return $usage;
        });
    }

    public function deleteUsage(MachineUsage $usage): void
    {
        $this->seasons->ensureOpen($usage->stage->season);
        $usage->delete();
    }

    public static function notionalCategory(): ExpenseCategory
    {
        $parent = ExpenseCategory::query()->whereNull('farm_id')->whereNull('parent_id')->where('name', 'Machinery hire')->firstOrFail();

        return ExpenseCategory::query()->firstOrCreate(
            ['farm_id' => null, 'parent_id' => $parent->id, 'name' => self::NOTIONAL_CATEGORY],
            ['cost_type' => 'machinery', 'default_unit' => 'hour', 'is_active' => true],
        );
    }
}
