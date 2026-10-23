<?php

namespace Tests\Feature\Phase3;

use App\Models\Expense;
use App\Models\Machine;
use App\Models\MachineUsage;
use App\Models\RecurringRule;
use App\Models\SharedCost;
use App\Services\PaymentService;
use Tests\TestCase;

class MachineryAndRecurringTest extends TestCase
{
    public function test_shared_repair_is_split_by_machine_hours_as_in_the_plan(): void
    {
        $owner = $this->user();
        $a = $this->season($owner);
        $b = $this->season($owner, ['name' => 'February 2027', 'start_date' => '2027-02-01']);
        $tractor = Machine::create(['name' => 'MF-385', 'type' => 'tractor', 'ownership' => 'owned']);
        $stageA = $this->stage($a, 'LAND_PREP');
        $stageB = $this->stage($b, 'LAND_PREP');

        $this->post(route('machinery.usage.store'), ['resource_id' => $tractor->id, 'season_stage_id' => $stageA->id, 'usage_date' => '2026-10-02', 'hours' => 24])->assertRedirect();
        $this->post(route('machinery.usage.store'), ['resource_id' => $tractor->id, 'season_stage_id' => $stageB->id, 'usage_date' => '2026-10-03', 'hours' => 16])->assertRedirect();

        $this->post(route('machinery.shared.store'), [
            'cost_date' => '2026-10-10', 'category_id' => $this->category('Tractor repair'), 'resource_id' => $tractor->id,
            'description' => 'Tractor repair', 'total_amount' => 12000, 'basis' => 'hours',
        ]);
        $cost = SharedCost::sole();
        $this->post(route('machinery.shared.allocate', $cost), ['stages' => [$stageA->id, $stageB->id]])->assertRedirect(route('machinery.index'));

        $this->assertSame('allocated', $cost->fresh()->status);
        $this->assertEquals(7200, Expense::where('season_stage_id', $stageA->id)->value('amount'));
        $this->assertEquals(4800, Expense::where('season_stage_id', $stageB->id)->value('amount'));
        $this->assertSame(2, Expense::where('shared_cost_id', $cost->id)->count());
    }

    public function test_manual_split_must_add_up_to_100_and_rounding_never_loses_a_rupee(): void
    {
        $owner = $this->user();
        $season = $this->season($owner);
        $stages = $season->stages()->pluck('id')->take(3)->all();
        $cost = SharedCost::create(['category_id' => $this->category('Tractor repair'), 'cost_date' => '2026-10-10', 'description' => 'Battery', 'total_amount' => 10000, 'basis' => 'manual', 'status' => 'draft']);

        $this->post(route('machinery.shared.allocate', $cost), ['stages' => $stages, 'manual' => [$stages[0] => 50, $stages[1] => 30, $stages[2] => 10]])->assertSessionHasErrors('manual');

        $equal = SharedCost::create(['category_id' => $this->category('Tractor repair'), 'cost_date' => '2026-10-10', 'description' => 'Tyres', 'total_amount' => 10000, 'basis' => 'equal', 'status' => 'draft']);
        $this->post(route('machinery.shared.allocate', $equal), ['stages' => $stages])->assertRedirect();
        $this->assertEquals(10000, Expense::where('shared_cost_id', $equal->id)->sum('amount'));
    }

    public function test_undoing_a_split_removes_its_lines_unless_paid(): void
    {
        $owner = $this->user();
        $season = $this->season($owner);
        $stage = $this->stage($season, 'LAND_PREP');
        $cost = SharedCost::create(['category_id' => $this->category('Tractor repair'), 'cost_date' => '2026-10-10', 'description' => 'Repair', 'total_amount' => 6000, 'basis' => 'equal', 'status' => 'draft']);
        $this->post(route('machinery.shared.allocate', $cost), ['stages' => [$stage->id]]);

        app(PaymentService::class)->payExpense(Expense::sole(), 1000, '2026-10-11', 'cash');
        $this->post(route('machinery.shared.unallocate', $cost))->assertSessionHasErrors('shared_cost');

        Expense::sole()->allocations()->first()->payment->delete();
        $this->post(route('machinery.shared.unallocate', $cost))->assertRedirect();
        $this->assertSame(0, Expense::withTrashed()->count());
        $this->assertSame('draft', $cost->fresh()->status);
    }

    public function test_notional_machine_cost_counts_as_economic_cost_only(): void
    {
        $owner = $this->user();
        $season = $this->season($owner);
        $tractor = Machine::create(['name' => 'MF-385', 'type' => 'tractor', 'ownership' => 'owned', 'internal_rate_per_hour' => 1500]);

        $this->post(route('machinery.usage.store'), ['resource_id' => $tractor->id, 'season_stage_id' => $this->stage($season, 'LAND_PREP')->id, 'usage_date' => '2026-10-02', 'hours' => 10, 'add_notional' => 1])->assertRedirect();

        $summary = $season->summary()->first();
        $this->assertEquals(0, $summary->total_cost);
        $this->assertEquals(15000, $summary->economic_cost);
        $this->assertEquals(0, $summary->outstanding);
        $this->assertSame(1, MachineUsage::count());
    }

    public function test_recurring_rule_creates_drafts_that_are_confirmed_or_skipped(): void
    {
        $owner = $this->user();
        $season = $this->season($owner, ['start_date' => now()->subMonths(2)->toDateString()]);
        $stage = $this->stage($season, 'GROWING');

        $this->post(route('recurring.store'), [
            'season_stage_id' => $stage->id, 'category_id' => $this->category('Tubewell electricity'), 'description' => 'Tubewell electricity',
            'amount' => 14500, 'frequency' => 'monthly', 'start_date' => now()->subMonth()->toDateString(), 'end_date' => now()->addMonths(1)->toDateString(),
        ])->assertRedirect();
        $rule = RecurringRule::sole();

        $this->get(route('recurring.index'))->assertOk()->assertSee('Tubewell electricity · ');
        $this->post(route('recurring.confirm', $rule), ['amount' => 15210, 'expense_date' => now()->subMonth()->toDateString()])->assertRedirect();

        $expense = Expense::sole();
        $this->assertEquals(15210, $expense->amount);
        $this->assertSame($rule->id, $expense->recurring_rule_id);
        $this->assertSame(now()->toDateString(), $rule->fresh()->next_run_date->toDateString());

        $this->post(route('recurring.skip', $rule))->assertRedirect();
        $this->assertSame(1, Expense::count());
        $this->post(route('recurring.skip', $rule));
        $this->assertFalse($rule->fresh()->is_active);
    }

    public function test_data_entry_can_log_hours_but_not_split_costs_or_confirm_drafts(): void
    {
        $owner = $this->user();
        $season = $this->season($owner);
        $munshi = $this->user('data_entry');
        $tractor = Machine::create(['name' => 'MF-385', 'type' => 'tractor', 'ownership' => 'owned']);
        $rule = RecurringRule::create(['season_stage_id' => $this->stage($season, 'GROWING')->id, 'category_id' => $this->category('Tubewell electricity'), 'description' => 'Power', 'amount' => 100, 'frequency' => 'monthly', 'start_date' => '2026-10-01', 'next_run_date' => '2026-10-01', 'is_active' => true]);

        $this->actingAs($munshi)->post(route('machinery.usage.store'), ['resource_id' => $tractor->id, 'season_stage_id' => $this->stage($season, 'LAND_PREP')->id, 'usage_date' => '2026-10-02', 'hours' => 3])->assertRedirect();
        $this->actingAs($munshi)->post(route('machinery.shared.store'), ['cost_date' => '2026-10-10', 'category_id' => $this->category('Tractor repair'), 'description' => 'x', 'total_amount' => 1, 'basis' => 'equal'])->assertForbidden();
        $this->actingAs($munshi)->post(route('recurring.confirm', $rule), ['amount' => 100, 'expense_date' => '2026-10-01'])->assertForbidden();
    }
}
