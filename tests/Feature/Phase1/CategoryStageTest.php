<?php

namespace Tests\Feature\Phase1;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\StageTemplate;
use Tests\TestCase;

class CategoryStageTest extends TestCase
{
    private function template(string $code): int
    {
        return StageTemplate::query()->where('code', $code)->value('id');
    }

    private function addCategory(string $name, array $stages): ExpenseCategory
    {
        $this->post(route('settings.categories.store'), [
            'name' => $name, 'cost_type' => 'machinery', 'parent_id' => ExpenseCategory::where('name', 'Machinery hire')->value('id'),
            'all_stages' => $stages ? '0' : '1', 'stage_ids' => array_map([$this, 'template'], $stages),
        ])->assertSessionHasNoErrors()->assertRedirect();

        return ExpenseCategory::firstWhere('name', $name);
    }

    public function test_category_can_be_linked_to_stages_or_all_stages(): void
    {
        $this->actingAs($this->user());

        $this->assertSame([$this->template('HARVEST')], $this->addCategory('Chopper hire', ['HARVEST'])->stageIds());
        $this->assertSame([], $this->addCategory('Tractor hire', [])->stageIds());

        $this->post(route('settings.categories.store'), ['name' => 'Nothing', 'cost_type' => 'other', 'all_stages' => '0'])
            ->assertSessionHasErrors('stage_ids');
    }

    public function test_expense_category_must_belong_to_the_stage(): void
    {
        $owner = $this->user();
        $season = $this->season($owner);
        $category = $this->addCategory('Chopper hire', ['HARVEST']);
        $payload = fn (string $stage) => ['season_stage_id' => $this->stage($season, $stage)->id, 'category_id' => $category->id, 'expense_date' => '2026-10-10', 'description' => 'Chopper', 'amount' => 1000];

        $this->post(route('expenses.store'), $payload('LAND_PREP'))->assertSessionHasErrors('category_id');
        $this->post(route('expenses.store'), $payload('HARVEST'))->assertSessionHasNoErrors();
        $this->assertSame(1, Expense::where('category_id', $category->id)->count());
    }

    public function test_group_stages_limit_its_children_and_the_form_marks_them(): void
    {
        $owner = $this->user();
        $season = $this->season($owner);
        $group = ExpenseCategory::create(['name' => 'Harvest costs', 'cost_type' => 'service']);
        $group->stages()->sync([$this->template('HARVEST')]);
        $child = ExpenseCategory::create(['name' => 'Loader', 'cost_type' => 'service', 'parent_id' => $group->id]);

        $this->assertFalse($child->fresh()->availableFor($this->template('LAND_PREP')));
        $this->assertTrue($child->fresh()->availableFor($this->template('HARVEST')));

        $this->get(route('expenses.create', $season))->assertOk()
            ->assertSee('data-stages="'.$this->template('HARVEST').'"', false);
    }

    public function test_editing_keeps_an_expense_category_that_was_restricted_later(): void
    {
        $owner = $this->user();
        $season = $this->season($owner);
        $stage = $this->stage($season, 'LAND_PREP');
        $this->post(route('expenses.store'), ['season_stage_id' => $stage->id, 'category_id' => $this->category('Diesel'), 'expense_date' => '2026-10-10', 'description' => 'Diesel', 'amount' => 500])->assertSessionHasNoErrors();
        $expense = Expense::latest('id')->first();

        ExpenseCategory::find($this->category('Diesel'))->stages()->sync([$this->template('HARVEST')]);

        $this->put(route('expenses.update', $expense), ['season_stage_id' => $stage->id, 'category_id' => $expense->category_id, 'expense_date' => '2026-10-10', 'description' => 'Diesel', 'amount' => 600])
            ->assertSessionHasNoErrors();
    }
}
