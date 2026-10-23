<?php

namespace Tests\Feature\Phase3;

use App\Models\Expense;
use Illuminate\Support\Str;
use Tests\TestCase;

class OfflineAndLocaleTest extends TestCase
{
    private function entry(int $stageId, array $overrides = []): array
    {
        return ['uuid' => (string) Str::uuid(), 'data' => $overrides + [
            'season_stage_id' => $stageId, 'category_id' => $this->category('Diesel'), 'expense_date' => '2026-10-12',
            'description' => 'Offline diesel', 'quantity' => 20, 'unit_code' => 'litre', 'unit_price' => 265,
        ]];
    }

    public function test_offline_entries_sync_once_and_report_errors_per_entry(): void
    {
        $owner = $this->user();
        $stage = $this->stage($this->season($owner), 'LAND_PREP');
        $munshi = $this->user('data_entry');
        $good = $this->entry($stage->id);
        $bad = $this->entry($stage->id, ['description' => '']);

        $response = $this->actingAs($munshi)->postJson(route('offline.sync'), ['entries' => [$good, $bad]])->assertOk();
        $this->assertTrue($response->json('results.0.ok'));
        $this->assertFalse($response->json('results.1.ok'));
        $this->assertNotEmpty($response->json('results.1.errors'));

        $this->actingAs($munshi)->postJson(route('offline.sync'), ['entries' => [$good]])->assertOk()->assertJsonPath('results.0.duplicate', true);

        $this->assertSame(1, Expense::count());
        $this->assertEquals(5300, Expense::sole()->amount);
        $this->assertSame($munshi->id, Expense::sole()->created_by);
    }

    public function test_viewers_cannot_sync_offline_entries(): void
    {
        $stage = $this->stage($this->season($this->user()), 'LAND_PREP');

        $this->actingAs($this->user('viewer'))->postJson(route('offline.sync'), ['entries' => [$this->entry($stage->id)]])->assertForbidden();
    }

    public function test_offline_sync_respects_closed_seasons(): void
    {
        $owner = $this->user();
        $season = $this->season($owner);
        $stage = $this->stage($season, 'LAND_PREP');
        $season->update(['status' => 'closed']);

        $this->actingAs($owner)->postJson(route('offline.sync'), ['entries' => [$this->entry($stage->id)]])
            ->assertOk()->assertJsonPath('results.0.ok', false);
    }

    public function test_switching_to_urdu_is_remembered_and_renders_right_to_left(): void
    {
        $owner = $this->user();

        $this->actingAs($owner)->post(route('locale'), ['locale' => 'ur'])->assertRedirect();

        $this->assertSame('ur', $owner->fresh()->locale);
        $this->actingAs($owner->fresh())->get(route('dashboard'))->assertOk()->assertSee('dir="rtl"', false)->assertSee('lang="ur"', false);
    }

    public function test_pdf_exports_stay_in_english_for_urdu_users(): void
    {
        $owner = $this->user(attributes: ['locale' => 'ur']);
        $season = $this->season($owner);

        $this->actingAs($owner)->get(route('reports.season', [$season, 'format' => 'pdf']))->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertSame('ur', app()->getLocale());
    }
}
