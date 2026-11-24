<?php

namespace Tests\Feature\Phase3;

use App\Models\CropSeason;
use App\Models\Machine;
use App\Models\SeasonStage;
use App\Models\User;
use App\Models\Vendor;
use App\Services\ExpenseService;
use Tests\TestCase;

class PaginationTest extends TestCase
{
    private User $owner;

    private CropSeason $season;

    private SeasonStage $stage;

    protected function setUp(): void
    {
        parent::setUp();
        $this->owner = $this->user();
        $this->actingAs($this->owner);
        $this->season = $this->season($this->owner);
        $this->stage = $this->stage($this->season, 'LAND_PREP');
    }

    /** Creates $count bills dated one day apart; "Bill 01" is the oldest. */
    private function bills(int $count, array $extra = []): void
    {
        for ($i = 1; $i <= $count; $i++) {
            app(ExpenseService::class)->save($extra + [
                'season_stage_id' => $this->stage->id, 'category_id' => $this->category('Diesel'),
                'expense_date' => now()->subDays(60 - $i)->toDateString(), 'description' => sprintf('Bill %02d', $i), 'amount' => 100,
            ]);
        }
    }

    public function test_stage_expenses_show_ten_per_page_with_next_pages_and_a_selectable_size(): void
    {
        $this->bills(23);
        $url = route('stages.show', [$this->season, $this->stage]);

        $this->get($url)->assertOk()
            ->assertViewHas('expenses', fn ($p) => $p->count() === 10 && $p->total() === 23 && $p->lastPage() === 3)
            ->assertSee('Bill 23')->assertDontSee('Bill 13')
            ->assertSee('23 lines')->assertSee('Showing 1–10 of 23')->assertSee('Rows per page')->assertSee('page=2#expenses', false);

        $this->get($url.'?page=3')->assertOk()->assertSee('Bill 01')->assertDontSee('Bill 04')->assertSee('Showing 21–23 of 23');
        $this->get($url.'?page=99')->assertOk()->assertViewHas('expenses', fn ($p) => $p->currentPage() === 3);

        // A bigger page size shows everything and is remembered for the next visit.
        $this->get($url.'?per_page=25')->assertOk()->assertViewHas('expenses', fn ($p) => $p->count() === 23)->assertDontSee('Showing 1–10');
        $this->get($url)->assertOk()->assertViewHas('expenses', fn ($p) => $p->perPage() === 25);

        // Unsupported sizes fall back to the remembered/default size.
        $this->get($url.'?per_page=5000')->assertOk()->assertViewHas('expenses', fn ($p) => $p->perPage() === 25);
    }

    public function test_short_lists_have_no_pager(): void
    {
        $this->bills(4);

        $this->get(route('stages.show', [$this->season, $this->stage]))->assertOk()
            ->assertSee('Bill 04')->assertDontSee('Rows per page');
    }

    public function test_machine_page_pages_bills_but_totals_cover_every_bill(): void
    {
        $tractor = Machine::create(['name' => 'Tractor', 'type' => 'tractor', 'ownership' => 'owned']);
        $this->bills(12, ['resource_id' => $tractor->id]);

        $this->get(route('machinery.machines.show', $tractor))->assertOk()
            ->assertViewHas('bills', fn ($p) => $p->count() === 10 && $p->total() === 12)
            ->assertViewHas('summary', fn ($s) => $s->count === 12 && $s->amount === 1200.0)
            ->assertSee('page=2#bills', false);

        // Filters stay in the page links.
        $this->get(route('machinery.machines.show', [$tractor, 'status' => 'unpaid']))->assertOk()
            ->assertSee('status=unpaid&amp;page=2#bills', false);
    }

    public function test_season_report_pages_on_screen_but_exports_keep_every_line(): void
    {
        $this->bills(15);

        $this->get(route('reports.season', $this->season))->assertOk()
            ->assertViewHas('expenses', fn ($p) => $p->count() === 10 && $p->total() === 15)
            ->assertSee('15 lines');
        $this->get(route('reports.season', [$this->season, 'format' => 'pdf']))->assertOk();
    }

    public function test_each_vendor_on_payables_pages_separately_and_payment_dialog_keeps_all_bills(): void
    {
        $vendor = Vendor::create(['name' => 'PSO Renala', 'type' => 'supplier']);
        $this->bills(13, ['vendor_id' => $vendor->id]);

        $this->get(route('payables.index'))->assertOk()
            ->assertViewHas('groups', fn ($groups) => $groups->first()->page->count() === 10
                && $groups->first()->page->total() === 13
                && $groups->first()->bills->count() === 13)
            ->assertSee('13 bills')
            ->assertSee('vendor_'.$vendor->id.'=2#vendor-'.$vendor->id, false);

        $this->get(route('payables.index', ['vendor_'.$vendor->id => 2]))->assertOk()
            ->assertViewHas('groups', fn ($groups) => $groups->first()->page->count() === 3);
    }
}
