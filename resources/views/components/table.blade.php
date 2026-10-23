@props(['minWidth' => 'min-w-[760px]'])

<div class="scrollbar-thin relative overflow-x-auto">
    <table {{ $attributes->class([
        'w-full text-start text-sm',
        $minWidth,
        '[&_th]:px-4 [&_th]:py-2.5 [&_th]:font-medium [&_th]:whitespace-nowrap [&_th:first-child]:ps-5 [&_th:last-child]:pe-5',
        '[&_td]:px-4 [&_td]:py-3 [&_td]:align-middle [&_td:first-child]:ps-5 [&_td:last-child]:pe-5',
    ]) }}>
        <thead class="border-b border-stone-100 bg-stone-50/70 text-xs tracking-wide text-stone-500 uppercase">
            {{ $head }}
        </thead>
        <tbody class="divide-y divide-stone-100 text-stone-700 [&>tr]:transition-colors [&>tr:hover]:bg-stone-50/70">
            {{ $slot }}
        </tbody>
    </table>
</div>
