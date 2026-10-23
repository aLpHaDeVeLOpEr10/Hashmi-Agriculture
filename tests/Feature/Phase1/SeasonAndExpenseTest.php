<?php

namespace Tests\Feature\Phase1;

use App\Models\AuditLog;
use App\Models\CropSeason;
use App\Models\Expense;
use App\Models\Field;
use App\Services\ExpenseService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SeasonAndExpenseTest extends TestCase
{
    public function test_creating_a_season_assigns_a_code_and_the_five_standard_stages(): void
    {
        $owner = $this->user();
        $this->actingAs($owner);
        $field = Field::create(['name' => 'North A-1', 'area_acres' => 12]);

        $this->actingAs($owner)->post(route('seasons.store'), [
            'name' => 'October 2026',
            'crop_id' => 1,
            'start_date' => '2026-10-05',
            'fields' => [$field->id => '10'],
        ])->assertRedirect(route('seasons.show', 'CS-2026-10'));

        $season = CropSeason::firstWhere('code', 'CS-2026-10');
        $this->assertSame('planned', $season->status);
        $this->assertEquals(10, $season->total_area_acres);
        $this->assertSame(
            ['Land Preparation', 'Seed & Material Purchase', 'Crop Growing & Maintenance', 'Harvesting', 'Silage Production'],
            $season->stages()->with('template')->get()->map(fn ($s) => $s->template->name)->all(),
        );
        $this->assertTrue($season->stages->every(fn ($s) => $s->status === 'not_started'));
    }

    public function test_a_second_season_in_the_same_month_gets_a_suffixed_code(): void
    {
        $owner = $this->user();
        $this->season($owner);
        $second = $this->season($owner, ['name' => 'October 2026 B']);

        $this->assertSame('CS-2026-10-2', $second->code);
    }

    public function test_amount_is_filled_from_quantity_times_unit_price_and_progress_is_marked(): void
    {
        $owner = $this->user();
        $season = $this->season($owner);
        $stage = $this->stage($season, 'LAND_PREP');

        $this->actingAs($owner)->post(route('expenses.store'), [
            'season_stage_id' => $stage->id,
            'category_id' => $this->category('Diesel'),
            'expense_date' => '2026-10-12',
            'description' => 'Diesel for ploughing',
            'quantity' => 100,
            'unit_code' => 'litre',
            'unit_price' => 250,
        ])->assertRedirect();

        $expense = Expense::sole();
        $this->assertEquals(25000, $expense->amount);
        $this->assertSame($season->id, $expense->season_id);
        $this->assertSame('in_progress', $stage->fresh()->status);
        $this->assertSame('active', $season->fresh()->status);
    }

    public function test_a_typed_amount_overrides_the_calculation(): void
    {
        $owner = $this->user();
        $stage = $this->stage($this->season($owner), 'SEED_MAT');

        $this->actingAs($owner)->post(route('expenses.store'), [
            'season_stage_id' => $stage->id, 'category_id' => $this->category('DAP'), 'expense_date' => '2026-10-12',
            'description' => 'DAP with discount', 'quantity' => 10, 'unit_code' => 'bag', 'unit_price' => 12000, 'amount' => 118500,
        ]);

        $this->assertEquals(118500, Expense::sole()->amount);
    }

    public function test_an_expense_without_amount_or_rate_is_rejected(): void
    {
        $owner = $this->user();
        $stage = $this->stage($this->season($owner), 'SEED_MAT');

        $this->actingAs($owner)->post(route('expenses.store'), [
            'season_stage_id' => $stage->id, 'category_id' => $this->category('DAP'), 'expense_date' => '2026-10-12', 'description' => 'No amount',
        ])->assertSessionHasErrors('amount');

        $this->assertSame(0, Expense::count());
    }

    public function test_the_database_rejects_an_expense_whose_season_differs_from_its_stage(): void
    {
        $owner = $this->user();
        $a = $this->season($owner);
        $b = $this->season($owner, ['name' => 'Other']);

        $this->expectException(QueryException::class);
        DB::table('expenses')->insert([
            'farm_id' => $this->farm->id, 'season_id' => $b->id, 'season_stage_id' => $this->stage($a, 'LAND_PREP')->id,
            'category_id' => $this->category('Diesel'), 'expense_date' => '2026-10-01', 'description' => 'Wrong season', 'amount' => 100,
        ]);
    }

    public function test_plan_sample_produces_the_documented_totals(): void
    {
        $owner = $this->user();
        $season = $this->season($owner);
        $service = app(ExpenseService::class);
        $lines = [
            'LAND_PREP' => [25000, 10000, 5000],
            'SEED_MAT' => [40000, 15000],
            'GROWING' => [20000, 18000],
            'HARVEST' => [30000, 12000],
            'SILAGE' => [25000, 20000],
        ];
        foreach ($lines as $code => $amounts) {
            foreach ($amounts as $amount) {
                $service->save(['season_stage_id' => $this->stage($season, $code)->id, 'category_id' => $this->category('Miscellaneous'), 'expense_date' => '2026-10-10', 'description' => 'Line', 'amount' => $amount]);
            }
        }

        $summary = $season->summary()->first();
        $this->assertEquals(220000, $summary->total_cost);
        $this->assertEquals(22000, $summary->cost_per_acre);
        $this->assertEquals(
            [40000, 55000, 38000, 42000, 45000],
            $season->stages()->with('totals')->get()->map(fn ($s) => (float) $s->totals->total)->all(),
        );
    }

    public function test_soft_deleted_expenses_drop_out_of_totals_and_are_audited(): void
    {
        $owner = $this->user();
        $season = $this->season($owner);
        $expense = app(ExpenseService::class)->save(['season_stage_id' => $this->stage($season, 'LAND_PREP')->id, 'category_id' => $this->category('Diesel'), 'expense_date' => '2026-10-10', 'description' => 'Diesel', 'amount' => 5000]);

        $this->actingAs($owner)->delete(route('expenses.destroy', $expense))->assertRedirect();

        $this->assertNotNull(Expense::withTrashed()->find($expense->id)->deleted_at);
        $this->assertEquals(0, $season->summary()->first()->total_cost);
        $this->assertTrue(AuditLog::where('table_name', 'expenses')->where('record_id', $expense->id)->where('action', 'delete')->exists());
        $this->assertSame($owner->id, AuditLog::where('table_name', 'expenses')->where('action', 'insert')->value('changed_by'));
    }

    public function test_a_closed_season_rejects_new_and_edited_expenses(): void
    {
        $owner = $this->user();
        $season = $this->season($owner);
        $stage = $this->stage($season, 'LAND_PREP');
        $expense = app(ExpenseService::class)->save(['season_stage_id' => $stage->id, 'category_id' => $this->category('Diesel'), 'expense_date' => '2026-10-10', 'description' => 'Diesel', 'amount' => 5000]);
        $season->update(['status' => 'closed', 'closed_at' => '2026-10-20']);

        $payload = ['season_stage_id' => $stage->id, 'category_id' => $this->category('Diesel'), 'expense_date' => '2026-10-11', 'description' => 'Late bill', 'amount' => 900];
        $this->actingAs($owner)->post(route('expenses.store'), $payload)->assertSessionHasErrors('season');
        $this->actingAs($owner)->put(route('expenses.update', $expense), $payload)->assertSessionHasErrors('season');
        $this->actingAs($owner)->delete(route('expenses.destroy', $expense))->assertSessionHasErrors('season');

        $this->assertSame(1, Expense::count());
        $this->assertEquals(5000, $expense->fresh()->amount);
    }

    public function test_custom_stage_can_be_added_to_one_season(): void
    {
        $owner = $this->user();
        $season = $this->season($owner);
        $other = $this->season($owner, ['name' => 'Other']);

        $this->actingAs($owner)->post(route('seasons.stages.store', $season), ['name' => 'Second cutting'])->assertRedirect();

        $this->assertSame(6, $season->stages()->count());
        $this->assertSame(5, $other->stages()->count());
        $this->assertSame('Second cutting', $season->stages()->with('template')->get()->last()->template->name);
    }
}
