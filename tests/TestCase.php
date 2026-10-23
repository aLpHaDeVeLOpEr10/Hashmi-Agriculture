<?php

namespace Tests;

use App\Models\Crop;
use App\Models\CropSeason;
use App\Models\ExpenseCategory;
use App\Models\Farm;
use App\Models\Field;
use App\Models\SeasonStage;
use App\Models\User;
use App\Services\SeasonService;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected Farm $farm;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ReferenceDataSeeder::class);
        $this->farm = Farm::create(['name' => 'Test Farm']);
    }

    protected function user(string $role = 'owner', ?Farm $farm = null, array $attributes = []): User
    {
        $user = new User($attributes + [
            'name' => ucfirst($role).' User',
            'phone' => '03'.random_int(100000000, 999999999),
            'role' => $role,
            'password' => 'password',
        ]);
        $user->farm_id = ($farm ?? $this->farm)->id;
        $user->save();

        return $user;
    }

    protected function season(User $as, array $overrides = []): CropSeason
    {
        $this->actingAs($as);
        $field = Field::create(['name' => 'Field '.random_int(1, 9999), 'area_acres' => 10]);

        return app(SeasonService::class)->create($overrides + [
            'name' => 'October 2026',
            'crop_id' => Crop::where('name', 'Maize')->value('id'),
            'start_date' => '2026-10-01',
            'fields' => [$field->id => 10],
        ]);
    }

    protected function stage(CropSeason $season, string $code): SeasonStage
    {
        return $season->stages()->whereHas('template', fn ($q) => $q->where('code', $code))->firstOrFail();
    }

    protected function category(string $name): int
    {
        return ExpenseCategory::query()->where('name', $name)->whereNotNull('parent_id')->value('id');
    }
}
