<?php

namespace App\Support;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Pagination for the expense tables: 10 rows by default, user-selectable page size
 * (remembered for the session), and out-of-range page numbers fall back to the last page.
 */
class Pagination
{
    public const SIZES = [10, 25, 50, 100];

    public const DEFAULT = 10;

    public static function perPage(Request $request): int
    {
        $asked = (int) $request->query('per_page');
        if (in_array($asked, self::SIZES, true)) {
            $request->session()->put('per_page', $asked);

            return $asked;
        }

        $remembered = (int) $request->session()->get('per_page', self::DEFAULT);

        return in_array($remembered, self::SIZES, true) ? $remembered : self::DEFAULT;
    }

    /** Paginate a query (Eloquent or query builder). */
    public static function query(Builder $query, Request $request, string $pageName = 'page', ?string $fragment = null): LengthAwarePaginator
    {
        $perPage = self::perPage($request);
        $total = (clone $query)->toBase()->getCountForPagination();
        $page = self::page($request, $pageName, $total, $perPage);

        return self::finish(new LengthAwarePaginator($query->forPage($page, $perPage)->get(), $total, $perPage, $page,
            ['path' => $request->url(), 'pageName' => $pageName]), $fragment);
    }

    /** Paginate an already-loaded collection (used where totals are computed over the full set). */
    public static function collection(Collection $items, Request $request, string $pageName = 'page', ?string $fragment = null): LengthAwarePaginator
    {
        $perPage = self::perPage($request);
        $page = self::page($request, $pageName, $items->count(), $perPage);

        return self::finish(new LengthAwarePaginator($items->forPage($page, $perPage)->values(), $items->count(), $perPage, $page,
            ['path' => $request->url(), 'pageName' => $pageName]), $fragment);
    }

    private static function page(Request $request, string $pageName, int $total, int $perPage): int
    {
        $last = max(1, (int) ceil($total / $perPage));

        return min(max(1, (int) $request->query($pageName, 1)), $last);
    }

    private static function finish(LengthAwarePaginator $paginator, ?string $fragment): LengthAwarePaginator
    {
        $paginator->withQueryString();

        return $fragment ? $paginator->fragment($fragment) : $paginator;
    }
}
