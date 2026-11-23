<?php

namespace Tests\Feature\Phase2;

use App\Models\CropSeason;
use App\Models\HarvestRecord;
use App\Models\SilageBatch;
use App\Models\StorageUnit;
use App\Services\ExpenseService;
use Tests\TestCase;

class ProductionAndReportTest extends TestCase
{
    private function costedSeason(array $overrides = [], float $total = 220000): CropSeason
    {
        $season = $this->season($this->user(), $overrides);
        app(ExpenseService::class)->save([
            'season_stage_id' => $this->stage($season, 'LAND_PREP')->id, 'category_id' => $this->category('Miscellaneous'),
            'expense_date' => $season->start_date->toDateString(), 'description' => 'All costs', 'amount' => $total,
        ]);

        return $season;
    }

    public function test_trolley_count_harvest_is_weighed_and_moves_the_season_on(): void
    {
        $owner = $this->user();
        $season = $this->costedSeason();

        $this->actingAs($owner)->post(route('harvests.store', $season), [
            'harvest_date' => '2026-10-20', 'method' => 'trolley_count', 'trolley_count' => 40, 'avg_trolley_kg' => 5000,
        ])->assertRedirect();

        $this->assertEquals(200000, HarvestRecord::sole()->quantity_kg);
        $this->assertSame('harvested', $season->fresh()->status);
        $this->assertEquals(1100, $season->summary()->value('cost_per_tonne_harvested'));
    }

    public function test_silage_cost_uses_usable_weight_and_is_frozen_when_the_season_closes(): void
    {
        $owner = $this->user();
        $season = $this->costedSeason();
        $this->actingAs($owner);
        $bunker = StorageUnit::create(['name' => 'Bunker 1', 'type' => 'bunker', 'capacity_tonnes' => 200]);

        $this->actingAs($owner)->post(route('batches.store', $season), [
            'storage_unit_id' => $bunker->id, 'fill_start' => '2026-10-15', 'fresh_weight_kg' => 190000, 'method' => 'weighbridge', 'expected_loss_pct' => 10,
        ])->assertRedirect();

        $summary = $season->summary()->first();
        $this->assertEquals(171000, $summary->silage_usable_kg);
        $this->assertSame('1.2865', number_format((float) $summary->silage_cost_per_kg, 4));
        $this->assertSame(1287.0, round((float) $summary->silage_cost_per_tonne));

        $this->actingAs($owner)->post(route('seasons.close', $season))->assertRedirect();

        $this->assertSame('closed', $season->fresh()->status);
        $this->assertEquals(1.2865, SilageBatch::sole()->cost_per_kg);
    }

    public function test_closed_season_locks_production_edits_but_reopen_is_owner_only(): void
    {
        $owner = $this->user();
        $manager = $this->user('manager');
        $season = $this->costedSeason();
        $this->actingAs($owner);
        $bunker = StorageUnit::create(['name' => 'Bunker 1', 'type' => 'bunker', 'capacity_tonnes' => 200]);
        $this->post(route('batches.store', $season), ['storage_unit_id' => $bunker->id, 'fill_start' => '2026-10-15', 'fresh_weight_kg' => 100000, 'method' => 'weighbridge', 'expected_loss_pct' => 10]);
        $this->actingAs($manager)->post(route('seasons.close', $season));

        $this->actingAs($manager)->post(route('harvests.store', $season), ['harvest_date' => '2026-10-20', 'method' => 'weighbridge', 'quantity_kg' => 1000])->assertSessionHasErrors('season');
        $this->actingAs($manager)->post(route('seasons.reopen', $season))->assertForbidden();

        $this->actingAs($owner)->post(route('seasons.reopen', $season))->assertRedirect();
        $this->assertSame('ensiled', $season->fresh()->status);
        $this->assertNull(SilageBatch::sole()->cost_per_kg);
    }

    public function test_batches_move_through_sealed_feeding_and_finished_in_order(): void
    {
        $owner = $this->user();
        $season = $this->costedSeason();
        $this->actingAs($owner);
        $batch = SilageBatch::create(['season_id' => $season->id, 'storage_unit_id' => StorageUnit::create(['name' => 'B', 'type' => 'bunker', 'capacity_tonnes' => 50])->id, 'fill_start' => '2026-10-01', 'fresh_weight_kg' => 1000, 'method' => 'weighbridge', 'status' => 'filling']);

        $this->post(route('batches.advance', [$season, $batch]), ['status' => 'feeding', 'date' => '2026-10-10'])->assertSessionHasErrors('status');
        $this->post(route('batches.advance', [$season, $batch]), ['status' => 'sealed', 'date' => '2026-10-03'])->assertRedirect();
        $this->post(route('batches.advance', [$season, $batch]), ['status' => 'feeding', 'date' => '2026-10-20'])->assertRedirect();

        $batch->refresh();
        $this->assertSame('feeding', $batch->status);
        $this->assertSame('2026-10-03', $batch->sealed_date->toDateString());
        $this->assertSame('2026-10-20', $batch->opened_date->toDateString());
    }

    public function test_season_comparison_reproduces_the_plan_example(): void
    {
        $owner = $this->user();
        $october = $this->costedSeason(['name' => 'October 2026', 'start_date' => '2026-10-01'], 220000);
        $february = $this->costedSeason(['name' => 'February 2027', 'start_date' => '2027-02-01'], 264000);

        $response = $this->actingAs($owner)->get(route('reports.comparison', ['seasons' => [$october->code, $february->code]]))->assertOk();

        $rows = collect($response->viewData('rows'))->keyBy('key');
        $this->assertSame([22000.0, 26400.0], $rows['cost_per_acre']['values']);
        $this->assertSame(20.0, $rows['cost_per_acre']['change'][1]);
    }

    public function test_reports_export_to_pdf_and_excel(): void
    {
        $owner = $this->user();
        $season = $this->costedSeason();
        $this->actingAs($owner);

        foreach ([
            route('reports.season', [$season, 'format' => 'pdf']),
            route('reports.comparison', ['format' => 'pdf']),
            route('reports.categories', ['format' => 'pdf']),
            route('reports.ageing', ['format' => 'pdf']),
        ] as $url) {
            $this->get($url)->assertOk()->assertHeader('content-type', 'application/pdf');
        }

        foreach ([
            route('reports.season', [$season, 'format' => 'xlsx']),
            route('reports.comparison', ['format' => 'xlsx']),
            route('reports.categories', ['format' => 'xlsx']),
            route('reports.ageing', ['format' => 'xlsx']),
        ] as $url) {
            $this->get($url)->assertOk()->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        }
    }

    public function test_season_report_can_be_filtered_to_one_stage(): void
    {
        $owner = $this->user();
        $season = $this->costedSeason([], 100000);
        $this->actingAs($owner);
        app(ExpenseService::class)->save([
            'season_stage_id' => $this->stage($season, 'HARVEST')->id, 'category_id' => $this->category('Miscellaneous'),
            'expense_date' => $season->start_date->toDateString(), 'description' => 'Plastic sheet roll', 'amount' => 32000,
        ]);

        $this->get(route('reports.season', $season))->assertOk()
            ->assertViewHas('kpis', fn ($k) => $k->total === 132000.0)
            ->assertSee('All costs')->assertSee('Plastic sheet roll');

        $this->get(route('reports.season', [$season, 'stage' => 'HARVEST']))->assertOk()
            ->assertViewHas('kpis', fn ($k) => $k->total === 32000.0 && $k->outstanding === 32000.0)
            ->assertViewHas('categories', fn ($c) => $c->sum('total') === 32000.0)
            ->assertSee('Stage total')->assertSee('Plastic sheet roll')->assertDontSee('All costs');

        $this->get(route('reports.season', [$season, 'stage' => 'HARVEST', 'format' => 'pdf']))->assertOk();
        $this->get(route('reports.season', [$season, 'stage' => 'HARVEST', 'format' => 'xlsx']))->assertOk();
        $this->get(route('reports.season', [$season, 'stage' => 'NOPE']))->assertSessionHasErrors('stage');
    }

    public function test_season_report_shows_unit_costs_and_data_quality(): void
    {
        $owner = $this->user();
        $season = $this->costedSeason([], 100000);
        $this->actingAs($owner);
        $service = app(ExpenseService::class);
        $growing = $this->stage($season, 'GROWING')->id;
        $service->save(['season_stage_id' => $growing, 'category_id' => $this->category('Miscellaneous'), 'expense_date' => $season->start_date->toDateString(), 'description' => 'Pesticide lump sum', 'amount' => 20000, 'completeness' => 'incomplete']);
        $service->save(['season_stage_id' => $growing, 'category_id' => $this->category('Miscellaneous'), 'expense_date' => $season->start_date->toDateString(), 'description' => 'Re-planting labour', 'amount' => 30000, 'is_rework' => true]);
        $season->harvests()->create(['harvest_date' => $season->start_date->toDateString(), 'method' => 'estimate', 'quantity_kg' => 20000]);

        $this->get(route('reports.season', $season))->assertOk()
            ->assertViewHas('kpis', fn ($k) => $k->lines === 3
                && $k->incomplete_lines === 1 && $k->incomplete_amount === 20000.0 && round($k->incomplete_share, 2) === 13.33
                && $k->rework_amount === 30000.0 && $k->provisional
                && round($k->cost_per_kg, 2) === 7.5 && round($k->cost_per_maund, 2) === 300.0)
            ->assertSee('Identified re-work')->assertSee('Cost / kg DM');

        $this->get(route('reports.season', [$season, 'stage' => 'GROWING']))->assertOk()
            ->assertViewHas('kpis', fn ($k) => $k->lines === 2 && $k->total === 50000.0 && round($k->incomplete_share, 1) === 40.0);
    }

    public function test_bunker_measurement_estimates_silage_weight_like_the_workbook(): void
    {
        $owner = $this->user();
        $season = $this->costedSeason();
        $this->actingAs($owner);
        $bunker = StorageUnit::create(['name' => 'Bunker 1', 'type' => 'bunker', 'capacity_tonnes' => 400]);

        $this->put(route('seasons.silage-settings', $season), ['benchmark_yield_maund' => 500, 'packing_density_kg_m3' => 650, 'dry_matter_pct' => 30])->assertRedirect();
        $this->assertEquals(500, $season->fresh()->benchmark_yield_maund);

        // 100 ft × 30 ft × 8 ft = 24,000 ft³ = 679.6032 m³ × 650 kg/m³ = 441,742.08 kg
        $this->post(route('batches.store', $season), [
            'storage_unit_id' => $bunker->id, 'fill_start' => '2026-10-15', 'method' => 'pit_volume',
            'length_ft' => 100, 'width_ft' => 30, 'height_ft' => 8, 'expected_loss_pct' => 10, 'fresh_weight_kg' => 1,
        ])->assertSessionHasNoErrors()->assertRedirect();

        $batch = SilageBatch::sole();
        $this->assertEquals(441742.08, (float) $batch->fresh_weight_kg);
        $this->assertEquals(650, $batch->density_kg_m3);
        $this->assertTrue($batch->isMeasured());

        $this->post(route('batches.store', $season), ['storage_unit_id' => $bunker->id, 'fill_start' => '2026-10-15', 'method' => 'pit_volume', 'expected_loss_pct' => 10])
            ->assertSessionHasErrors(['length_ft', 'width_ft', 'height_ft']);

        $this->get(route('reports.season', $season))->assertOk()
            ->assertViewHas('kpis', fn ($k) => $k->benchmark_yield_maund === 500.0 && round($k->cost_per_kg_dm, 4) === round(220000 / (441742.08 * 0.3), 4));
        $this->get(route('production.index', ['season' => $season->code]))->assertOk()->assertSee('Bunker estimator')->assertSee('100 × 30 × 8 ft');
    }

    public function test_ageing_buckets_by_days_outstanding(): void
    {
        $owner = $this->user();
        $this->costedSeason(['start_date' => now()->subDays(100)->toDateString()], 5000);
        $this->costedSeason(['start_date' => now()->subDays(10)->toDateString(), 'name' => 'Recent'], 3000);

        $buckets = $this->actingAs($owner)->get(route('reports.ageing'))->assertOk()->viewData('buckets');

        $this->assertEquals(3000, $buckets['0–30']['amount']);
        $this->assertEquals(5000, $buckets['90+']['amount']);
    }
}
