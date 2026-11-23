<?php

namespace Tests\Feature\Phase1;

use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    public function test_every_role_can_open_their_profile(): void
    {
        foreach (['owner', 'manager', 'data_entry', 'viewer'] as $role) {
            $user = $this->user($role);
            $this->actingAs($user)->get(route('profile.edit'))->assertOk()->assertSee($user->name);
        }
    }

    public function test_user_can_update_name_and_phone(): void
    {
        $user = $this->user('viewer', attributes: ['phone' => '03001234567']);

        $this->actingAs($user)->put(route('profile.update'), ['name' => 'Ali Hashmi', 'phone' => '+92 321 7654321'])
            ->assertRedirect()->assertSessionHasNoErrors();

        $user->refresh();
        $this->assertSame('Ali Hashmi', $user->name);
        $this->assertSame('03217654321', $user->phone);
    }

    public function test_phone_must_be_unique_but_own_number_is_allowed(): void
    {
        $this->user(attributes: ['phone' => '03009999999']);
        $user = $this->user('manager', attributes: ['phone' => '03001234567']);

        $this->actingAs($user)->put(route('profile.update'), ['name' => 'Same', 'phone' => '03001234567'])->assertSessionHasNoErrors();
        $this->actingAs($user)->put(route('profile.update'), ['name' => 'Taken', 'phone' => '03009999999'])->assertSessionHasErrors('phone');
    }

    public function test_password_change_requires_current_password_and_confirmation(): void
    {
        $user = $this->user('data_entry');

        $this->actingAs($user)->put(route('profile.password'), ['current_password' => 'wrong', 'password' => 'newsecret1', 'password_confirmation' => 'newsecret1'])
            ->assertSessionHasErrors('current_password');
        $this->actingAs($user)->put(route('profile.password'), ['current_password' => 'password', 'password' => 'newsecret1', 'password_confirmation' => 'mismatch'])
            ->assertSessionHasErrors('password');

        $this->actingAs($user)->put(route('profile.password'), ['current_password' => 'password', 'password' => 'newsecret1', 'password_confirmation' => 'newsecret1'])
            ->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('newsecret1', $user->fresh()->password));
    }

    public function test_guests_cannot_reach_profile(): void
    {
        $this->get(route('profile.edit'))->assertRedirect(route('login'));
    }
}
