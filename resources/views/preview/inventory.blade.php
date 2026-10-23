@php
    $items = array_map(fn ($item) => $item + [
        'status' => $item['stock'] <= 0 ? 'Out of Stock' : ($item['stock'] <= $item['reorder'] ? 'Low Stock' : 'In Stock'),
        'level' => round($item['stock'] / $item['capacity'] * 100),
    ], $items);
    $alerts = array_filter($items, fn ($item) => $item['status'] !== 'In Stock');
    $categories = array_values(array_unique(array_column($items, 'category')));
    $ration = [
        'type' => 'hbar', 'unit' => 'kg', 'legend' => false,
        'labels' => ['Maize silage', 'Green fodder', 'Dairy concentrate', 'Wheat straw', 'Cotton seed cake', 'Mineral mixture'],
        'datasets' => [['label' => 'Per cow per day', 'data' => [25, 15, 4.5, 3, 1.5, 0.1]]],
    ];
@endphp

<x-layouts.app title="Inventory & Feed" :preview="true" :breadcrumbs="['Inventory & Feed']">
    <x-page-header description="Stock levels for feed, seed, fertilizer, chemicals and fuel with reorder alerts.">
        <x-slot:actions>
            <x-button variant="secondary" icon="arrow-left-right" x-on:click="$dispatch('open-modal', 'stock-movement')">Stock movement</x-button>
            <x-button icon="plus" x-on:click="$dispatch('open-modal', 'add-item')">Add item</x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card label="Stock value" value="Rs 18.4M" icon="package" :change="6.8" />
        <x-stat-card label="Feed reserve" value="96 days" icon="beef" hint="Silage-based ration, full herd" tone="teal" />
        <x-stat-card label="Low stock items" :value="count(array_filter($items, fn ($i) => $i['status'] === 'Low Stock'))" icon="triangle-alert" hint="At or below reorder level" tone="amber" />
        <x-stat-card label="Out of stock" :value="count(array_filter($items, fn ($i) => $i['status'] === 'Out of Stock'))" icon="package-x" hint="Purchase order needed" tone="rose" />
    </div>

    @if ($alerts)
        <div x-data="{ show: true }" x-show="show" class="mt-6 flex gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4">
            <x-icon name="triangle-alert" class="mt-0.5 size-5 shrink-0 text-amber-600" />
            <div class="min-w-0 flex-1 text-sm">
                <p class="font-semibold text-amber-900">{{ count($alerts) }} items need restocking</p>
                <p class="mt-0.5 text-amber-800">{{ implode(' · ', array_map(fn ($i) => $i['name'].' ('.$i['stock'].' '.$i['unit'].')', $alerts)) }}</p>
            </div>
            <button type="button" x-on:click="show = false" class="self-start rounded-md p-1 text-amber-700 hover:bg-amber-100" aria-label="Dismiss">
                <x-icon name="x" class="size-4" />
            </button>
        </div>
    @endif

    <x-card flush class="mt-6" x-data="filterable({ category: '', status: '' })">
        <div class="flex flex-col gap-3 border-b border-stone-100 px-5 py-4 2xl:flex-row 2xl:items-center 2xl:justify-between">
            <x-segmented model="filters.category" :options="['' => 'All items'] + array_combine($categories, $categories)" />
            <div class="flex flex-col gap-2 sm:flex-row">
                <x-search-input placeholder="Search item, SKU, supplier…" class="sm:w-60" />
                <x-filter-select model="status" :options="['In Stock', 'Low Stock', 'Out of Stock']" all="Any stock status" />
            </div>
        </div>
        <x-table min-width="min-w-[1000px]">
            <x-slot:head>
                <tr>
                    <th>Item</th>
                    <th>Category</th>
                    <th class="w-52">Stock level</th>
                    <th class="text-right">Reorder at</th>
                    <th>Location</th>
                    <th>Supplier</th>
                    <th>Status</th>
                    <th class="text-right"><span class="sr-only">Actions</span></th>
                </tr>
            </x-slot:head>
            @foreach ($items as $item)
                <tr data-row data-category="{{ $item['category'] }}" data-status="{{ $item['status'] }}" data-search="{{ strtolower($item['name'].' '.$item['sku'].' '.$item['supplier'].' '.$item['location']) }}" x-show="match($el)">
                    <td class="whitespace-nowrap">
                        <p class="font-medium text-stone-900">{{ $item['name'] }}</p>
                        <p class="font-mono text-xs text-stone-500">{{ $item['sku'] }} · updated {{ $item['updated'] }}</p>
                    </td>
                    <td>{{ $item['category'] }}</td>
                    <td>
                        <div class="flex items-baseline justify-between text-sm">
                            <span class="font-medium text-stone-900 tabular-nums">{{ number_format($item['stock'], is_float($item['stock']) ? 1 : 0) }} {{ $item['unit'] }}</span>
                            <span class="text-xs text-stone-400 tabular-nums">{{ $item['level'] }}%</span>
                        </div>
                        <div class="relative mt-1.5">
                            <x-progress :value="$item['level']" :tone="match ($item['status']) { 'Out of Stock' => 'red', 'Low Stock' => 'amber', default => 'brand' }" />
                            <span class="absolute -top-0.5 h-2.5 w-px bg-stone-500" style="left: {{ round($item['reorder'] / $item['capacity'] * 100) }}%" title="Reorder level"></span>
                        </div>
                    </td>
                    <td class="text-right whitespace-nowrap tabular-nums">{{ $item['reorder'] }} {{ $item['unit'] }}</td>
                    <td class="whitespace-nowrap">{{ $item['location'] }}</td>
                    <td class="whitespace-nowrap">{{ $item['supplier'] }}</td>
                    <td><x-badge :status="$item['status']" /></td>
                    <td class="text-right">
                        <x-button variant="ghost" size="sm" icon="plus" x-on:click="$dispatch('open-modal', 'stock-movement')">Restock</x-button>
                    </td>
                </tr>
            @endforeach
        </x-table>
        <x-filter-empty />
    </x-card>

    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-2">
        <x-card title="Daily ration — milking cow" description="As-fed kg per animal per day (TMR)">
            <x-chart :config="$ration" label="Bar chart of daily feed ration components per milking cow" height="h-64" />
        </x-card>

        <x-card title="Recent stock movements" flush>
            <ul class="divide-y divide-stone-100">
                @foreach ([
                    ['in', 'Urea (46% N)', '+50 bags', 'Purchase — Engro dealer', '22 Oct'],
                    ['out', 'Maize silage', '−14.3 t', 'Daily feed-out, Shed 1–3', '22 Oct'],
                    ['out', 'DAP (18-46-0)', '−30 bags', 'Applied to CV-2 potato', '21 Oct'],
                    ['in', 'Silage inoculant', '+5 kg', 'Purchase — Agri Biotech', '21 Oct'],
                    ['out', 'High-speed diesel', '−600 L', 'Forage harvester & tractors', '21 Oct'],
                ] as [$dir, $name, $qty, $note, $date])
                    <li class="flex items-center gap-3 px-5 py-3">
                        <span @class(['grid size-8 shrink-0 place-items-center rounded-full', 'bg-brand-50 text-brand-600' => $dir === 'in', 'bg-stone-100 text-stone-500' => $dir === 'out'])>
                            <x-icon :name="$dir === 'in' ? 'arrow-down-left' : 'arrow-up-right'" class="size-4" />
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-stone-900">{{ $name }}</p>
                            <p class="truncate text-xs text-stone-500">{{ $note }}</p>
                        </div>
                        <div class="text-right">
                            <p @class(['text-sm font-semibold tabular-nums', 'text-brand-700' => $dir === 'in', 'text-stone-700' => $dir === 'out'])>{{ $qty }}</p>
                            <p class="text-xs text-stone-400">{{ $date }}</p>
                        </div>
                    </li>
                @endforeach
            </ul>
        </x-card>
    </div>

    <x-modal name="add-item" title="Add inventory item" submit="Save item" message="Inventory item saved">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <x-form.input label="Item name" name="name" required placeholder="e.g. Wheat seed Dilkash-20" />
            </div>
            <x-form.select label="Category" name="category" required :options="$categories" />
            <x-form.select label="Unit" name="unit" required :options="['kg', 't', 'bags', 'L', 'units']" />
            <x-form.input label="Opening stock" name="stock" type="number" min="0" step="0.1" required />
            <x-form.input label="Reorder level" name="reorder" type="number" min="0" step="0.1" required />
            <x-form.select label="Storage location" name="location" :options="array_values(array_unique(array_column($items, 'location')))" />
            <x-form.input label="Supplier" name="supplier" placeholder="Supplier name" />
        </div>
    </x-modal>

    <x-modal name="stock-movement" title="Record stock movement" size="md" submit="Save movement" message="Stock movement recorded">
        <div class="grid grid-cols-1 gap-4" x-data="{ direction: 'in' }">
            <div class="grid grid-cols-2 gap-2 rounded-lg bg-stone-100 p-1" role="radiogroup">
                @foreach (['in' => 'Stock in', 'out' => 'Stock out'] as $value => $label)
                    <button type="button" x-on:click="direction = '{{ $value }}'" :class="direction === '{{ $value }}' ? 'bg-white text-stone-900 shadow-xs' : 'text-stone-500'"
                        class="rounded-md py-1.5 text-sm font-medium">{{ $label }}</button>
                @endforeach
            </div>
            <x-form.select label="Item" name="item" required :options="array_column($items, 'name')" />
            <div class="grid grid-cols-2 gap-4">
                <x-form.input label="Quantity" name="quantity" type="number" min="0.1" step="0.1" required />
                <x-form.input label="Date" name="date" type="date" required :value="now()->toDateString()" />
            </div>
            <x-form.input label="Reference" name="reference" placeholder="Invoice no. / field / shed" />
        </div>
    </x-modal>
</x-layouts.app>
