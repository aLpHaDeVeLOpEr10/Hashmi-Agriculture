<?php

namespace Tests\Feature\Phase1;

use App\Models\ExpenseCategory;
use App\Models\Farm;
use App\Services\ExpenseService;
use Tests\TestCase;

class AccessTest extends TestCase
{
    public function test_users_sign_in_with_phone_number_in_any_common_format(): void
    {
        $this->user(attributes: ['phone' => '03001234567']);

        $this->post('/login', ['phone' => '+92 300 1234567', 'password' => 'password'])->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
    }

    public function test_inactive_users_cannot_sign_in(): void
    {
        $this->user(attributes: ['phone' => '03001234567', 'is_active' => false]);

        $this->post('/login', ['phone' => '03001234567', 'password' => 'password'])->assertSessionHasErrors('phone');
        $this->assertGuest();
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->get(route('seasons.index'))->assertRedirect(route('login'));
    }

    public function test_every_page_renders_for_each_role(): void
    {
        $owner = $this->user();
        $season = $this->season($owner);
        $stage = $this->stage($season, 'LAND_PREP');
        app(ExpenseService::class)->save(['season_stage_id' => $stage->id, 'category_id' => $this->category('Diesel'), 'expense_date' => '2026-10-10', 'description' => 'Diesel', 'amount' => 5000]);

        foreach (['owner', 'manager', 'data_entry', 'viewer'] as $role) {
            $user = $role === 'owner' ? $owner : $this->user($role);
            foreach ([route('dashboard'), route('seasons.index'), route('seasons.show', $season), route('stages.show', [$season, $stage]), route('settings.categories.index'), route('preview.livestock')] as $url) {
                $this->actingAs($user)->get($url)->assertOk();
            }
        }
    }

    public function test_data_entry_can_add_but_not_edit_expenses_or_manage_seasons(): void
    {
        $owner = $this->user();
        $munshi = $this->user('data_entry');
        $season = $this->season($owner);
        $stage = $this->stage($season, 'LAND_PREP');
        $payload = ['season_stage_id' => $stage->id, 'category_id' => $this->category('Diesel'), 'expense_date' => '2026-10-10', 'description' => 'Diesel', 'amount' => 5000];

        $this->actingAs($munshi)->post(route('expenses.store'), $payload)->assertRedirect();
        $expense = $season->expenses()->sole();

        $this->actingAs($munshi)->get(route('expenses.edit', $expense))->assertForbidden();
        $this->actingAs($munshi)->put(route('expenses.update', $expense), $payload)->assertForbidden();
        $this->actingAs($munshi)->delete(route('expenses.destroy', $expense))->assertForbidden();
        $this->actingAs($munshi)->get(route('seasons.create'))->assertForbidden();
        $this->actingAs($munshi)->patch(route('seasons.status', $season), ['status' => 'harvested'])->assertForbidden();
    }

    public function test_viewers_are_read_only(): void
    {
        $owner = $this->user();
        $viewer = $this->user('viewer');
        $stage = $this->stage($this->season($owner), 'LAND_PREP');

        $this->actingAs($viewer)->post(route('expenses.store'), ['season_stage_id' => $stage->id, 'category_id' => $this->category('Diesel'), 'expense_date' => '2026-10-10', 'description' => 'x', 'amount' => 1])->assertForbidden();
        $this->actingAs($viewer)->post(route('settings.vendors.store'), ['name' => 'X', 'type' => 'supplier'])->assertForbidden();
        $this->actingAs($viewer)->get(route('settings.users.index'))->assertForbidden();
    }

    public function test_farms_cannot_see_each_others_seasons(): void
    {
        $season = $this->season($this->user());
        $outsider = $this->user('owner', Farm::create(['name' => 'Other farm']));

        $this->actingAs($outsider)->get(route('seasons.show', $season))->assertNotFound();
        $this->actingAs($outsider)->get(route('seasons.index'))->assertDontSee($season->code);
    }

    public function test_farm_categories_are_private_and_system_categories_are_locked(): void
    {
        $owner = $this->user();
        $this->actingAs($owner)->post(route('settings.categories.store'), ['name' => 'Generator fuel', 'cost_type' => 'fuel', 'parent_id' => ExpenseCategory::where('name', 'Fuel')->value('id')])->assertRedirect();

        $custom = ExpenseCategory::firstWhere('name', 'Generator fuel');
        $this->assertSame($this->farm->id, $custom->farm_id);

        $system = ExpenseCategory::firstWhere('name', 'Diesel');
        $this->actingAs($owner)->put(route('settings.categories.update', $system), ['name' => 'Hacked', 'cost_type' => 'fuel'])->assertForbidden();

        $outsider = $this->user('owner', Farm::create(['name' => 'Other farm']));
        $this->actingAs($outsider);
        $this->assertNull(ExpenseCategory::firstWhere('name', 'Generator fuel'));
    }

    public function test_owner_cannot_lock_themselves_out(): void
    {
        $owner = $this->user();

        $this->actingAs($owner)->put(route('settings.users.update', $owner), ['name' => $owner->name, 'role' => 'viewer'])->assertSessionHasErrors('role');
        $this->assertSame('owner', $owner->fresh()->role);
    }
}
