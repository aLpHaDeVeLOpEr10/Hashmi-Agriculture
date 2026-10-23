@props(['status' => null, 'tone' => null, 'dot' => true])

@php
    $map = [
        'green' => ['Growing', 'Completed', 'Active', 'Healthy', 'In Stock', 'Paid', 'Present', 'Ready to Feed', 'Income', 'Done', 'Good', 'Low', 'Lactating', 'Excellent', 'Ready'],
        'amber' => ['Ready for Harvest', 'Pending', 'Low Stock', 'Medium', 'Fermenting', 'On Leave', 'Under Observation', 'Due Soon', 'Partially Paid', 'Late', 'Fair'],
        'sky' => ['Planned', 'Scheduled', 'In Progress', 'Filling', 'Pregnant', 'Upcoming', 'Ongoing', 'Processing'],
        'red' => ['Overdue', 'High', 'Out of Stock', 'Under Treatment', 'Absent', 'Unpaid', 'Expense', 'Urgent', 'Critical', 'Poor'],
        'violet' => ['Sealed', 'Feeding Out', 'Dry', 'Harvested', 'Contract'],
        'stone' => ['Fallow', 'Inactive', 'Sold', 'Cancelled', 'Draft', 'Closed'],
    ];
    $tone ??= collect($map)->search(fn ($statuses) => in_array($status, $statuses, true)) ?: 'stone';
    $styles = [
        'green' => ['bg-brand-50 text-brand-800 ring-brand-600/15', 'bg-brand-500'],
        'amber' => ['bg-amber-50 text-amber-800 ring-amber-600/20', 'bg-amber-500'],
        'sky' => ['bg-sky-50 text-sky-800 ring-sky-600/15', 'bg-sky-500'],
        'red' => ['bg-red-50 text-red-700 ring-red-600/15', 'bg-red-500'],
        'violet' => ['bg-violet-50 text-violet-800 ring-violet-600/15', 'bg-violet-500'],
        'stone' => ['bg-stone-100 text-stone-700 ring-stone-500/15', 'bg-stone-400'],
    ][$tone];
@endphp

<span {{ $attributes->class(['inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-xs font-medium whitespace-nowrap ring-1 ring-inset', $styles[0]]) }}>
    @if ($dot)
        <span class="size-1.5 rounded-full {{ $styles[1] }}"></span>
    @endif
    {{ $slot->isEmpty() ? $status : $slot }}
</span>
