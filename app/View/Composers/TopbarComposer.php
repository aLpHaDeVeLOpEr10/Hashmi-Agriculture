<?php

namespace App\View\Composers;

use App\Models\CropSeason;
use App\Models\SharedCost;
use App\Services\RecurringService;
use App\Support\Money;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TopbarComposer
{
    public function compose(View $view): void
    {
        if (! Auth::check()) {
            return;
        }

        $seasons = CropSeason::query()->orderByDesc('start_date')->get(['id', 'code', 'name', 'status', 'expected_end_date']);

        $search = collect(config('agri.navigation'))
            ->map(fn ($item) => ['label' => __($item['label']), 'group' => __('Pages'), 'url' => route($item['route'])])
            ->merge($seasons->map(fn ($s) => ['label' => "{$s->name} ({$s->code})", 'group' => __('Seasons'), 'url' => route('seasons.show', $s)]));

        if (Auth::user()->can('manage-seasons')) {
            $search->push(['label' => __('New crop season'), 'group' => __('Quick actions'), 'url' => route('seasons.create')]);
        }

        $view->with([
            'searchItems' => $search->values(),
            'notifications' => $this->notifications($seasons),
        ]);
    }

    private function notifications($seasons): array
    {
        $items = [];

        foreach ($seasons as $season) {
            if ($season->status !== 'closed' && $season->expected_end_date?->isPast()) {
                $items[] = [
                    'icon' => 'calendar-clock', 'tone' => 'text-amber-600 bg-amber-50',
                    'title' => __(':name passed its expected end date', ['name' => $season->name]),
                    'body' => __('Enter the remaining bills, then close the season to freeze its costs.'),
                    'url' => route('seasons.show', $season),
                ];
            }
        }

        $unpaid = DB::table('v_expense_status')->where('farm_id', Auth::user()->farm_id)->where('balance', '>', 0)
            ->selectRaw('COUNT(*) as bills, COALESCE(SUM(balance), 0) as total')->first();

        if ($unpaid && $unpaid->bills > 0) {
            $items[] = [
                'icon' => 'receipt', 'tone' => 'text-red-600 bg-red-50',
                'title' => __(':amount unpaid', ['amount' => Money::format($unpaid->total)]),
                'body' => __(':count bills have an outstanding balance.', ['count' => $unpaid->bills]),
                'url' => route('payables.index'),
            ];
        }

        $due = app(RecurringService::class)->due()->count();
        if ($due > 0) {
            $items[] = [
                'icon' => 'repeat', 'tone' => 'text-sky-600 bg-sky-50',
                'title' => trans_choice(':count recurring cost to confirm|:count recurring costs to confirm', $due),
                'body' => __('Check this period’s amount and confirm or skip it.'),
                'url' => route('recurring.index'),
            ];
        }

        $drafts = SharedCost::query()->where('status', 'draft')->count();
        if ($drafts > 0) {
            $items[] = [
                'icon' => 'split', 'tone' => 'text-violet-600 bg-violet-50',
                'title' => trans_choice(':count shared cost waiting to be split|:count shared costs waiting to be split', $drafts),
                'body' => __('Split them across the stages that used the machine.'),
                'url' => route('machinery.index'),
            ];
        }

        return $items;
    }
}
