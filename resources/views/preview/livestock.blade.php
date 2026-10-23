@php
    $herd = [
        ['type' => 'Cattle', 'count' => 214, 'detail' => 'Sahiwal, HF cross, Cholistani', 'milking' => 96, 'tone' => 'bg-brand-500'],
        ['type' => 'Buffalo', 'count' => 96, 'detail' => 'Nili-Ravi', 'milking' => 52, 'tone' => 'bg-sky-500'],
        ['type' => 'Goat', 'count' => 58, 'detail' => 'Beetal, Teddy', 'milking' => 21, 'tone' => 'bg-amber-500'],
        ['type' => 'Sheep', 'count' => 18, 'detail' => 'Kajli', 'milking' => 0, 'tone' => 'bg-violet-500'],
    ];
    $total = array_sum(array_column($herd, 'count'));
    $milk = [
        'type' => 'line', 'unit' => 'L',
        'labels' => collect(range(13, 0))->map(fn ($d) => now()->subDays($d)->format('d M'))->all(),
        'datasets' => [
            ['label' => 'Cattle', 'data' => [1402, 1418, 1395, 1431, 1440, 1426, 1452, 1461, 1448, 1470, 1466, 1482, 1478, 1490], 'color' => 0],
            ['label' => 'Buffalo', 'data' => [602, 598, 611, 607, 615, 620, 612, 618, 626, 622, 631, 628, 634, 650], 'color' => 1],
        ],
    ];
    $schedule = [
        ['title' => 'FMD vaccination (booster)', 'group' => '310 cattle & buffalo', 'date' => '28 Oct', 'by' => 'Dr. Sana Iqbal', 'status' => 'Upcoming'],
        ['title' => 'Deworming — goats & sheep', 'group' => '76 small ruminants', 'date' => '30 Oct', 'by' => 'Muhammad Arif', 'status' => 'Upcoming'],
        ['title' => 'Pregnancy diagnosis', 'group' => '18 inseminated cows', 'date' => '02 Nov', 'by' => 'Dr. Sana Iqbal', 'status' => 'Scheduled'],
        ['title' => 'HS vaccination', 'group' => '96 buffalo', 'date' => '19 Oct', 'by' => 'Dr. Sana Iqbal', 'status' => 'Completed'],
        ['title' => 'Mastitis CMT screening', 'group' => 'Shed 2 milking herd', 'date' => '18 Oct', 'by' => 'Nadeem Abbas', 'status' => 'Completed'],
    ];
@endphp

<x-layouts.app title="Livestock Management" :preview="true" :breadcrumbs="['Livestock Management']">
    <x-page-header description="Herd register, milk production, breeding and animal health across sheds and farms.">
        <x-slot:actions>
            <x-button variant="secondary" icon="syringe" x-on:click="$dispatch('open-modal', 'health-record')">Health record</x-button>
            <x-button icon="plus" x-on:click="$dispatch('open-modal', 'add-livestock')">Add livestock</x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card label="Total livestock" :value="$total.' head'" icon="beef" :change="3.1" :trend="[362, 366, 371, 370, 377, 381, 386]" />
        <x-stat-card label="Milk today" value="2,140 L" icon="milk" :change="3.4" period="vs 7-day avg" :trend="[2004, 2016, 2006, 2038, 2055, 2046, 2140]" tone="sky" />
        <x-stat-card label="Pregnant animals" value="37" icon="heart-pulse" hint="9 calvings expected in Nov" tone="violet" />
        <x-stat-card label="Under treatment" value="4" icon="stethoscope" :change="-33.3" :invert="true" period="vs last week" tone="rose" />
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
        <x-card title="Herd composition" description="{{ $total }} animals across 7 sheds">
            <div class="flex h-3 overflow-hidden rounded-full">
                @foreach ($herd as $group)
                    <div class="{{ $group['tone'] }} border-r-2 border-white last:border-r-0" style="width: {{ $group['count'] / $total * 100 }}%" title="{{ $group['type'] }}: {{ $group['count'] }}"></div>
                @endforeach
            </div>
            <ul class="mt-5 space-y-4">
                @foreach ($herd as $group)
                    <li class="flex items-center gap-3">
                        <span class="size-2.5 shrink-0 rounded-sm {{ $group['tone'] }}"></span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-stone-800">{{ $group['type'] }}</p>
                            <p class="truncate text-xs text-stone-500">{{ $group['detail'] }}@if ($group['milking']) · {{ $group['milking'] }} in milk @endif</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold text-stone-900 tabular-nums">{{ $group['count'] }}</p>
                            <p class="text-xs text-stone-500 tabular-nums">{{ round($group['count'] / $total * 100) }}%</p>
                        </div>
                    </li>
                @endforeach
            </ul>
        </x-card>

        <x-card title="Milk production" description="Daily litres, last 14 days" class="xl:col-span-2">
            <x-slot:actions>
                <span class="text-sm text-stone-500">Avg yield <span class="font-semibold text-stone-800">14.6 L</span>/animal</span>
            </x-slot:actions>
            <x-chart :config="$milk" label="Line chart of daily milk production for cattle and buffalo" height="h-64" />
        </x-card>
    </div>

    <x-card title="Animal register" flush class="mt-6" x-data="filterable({ type: '', health: '' })">
        <div class="flex flex-col gap-3 border-b border-stone-100 px-5 py-4 2xl:flex-row 2xl:items-center 2xl:justify-between">
            <x-segmented model="filters.type" :options="['' => 'All', 'Cattle' => 'Cattle', 'Buffalo' => 'Buffalo', 'Goat' => 'Goats', 'Sheep' => 'Sheep']" />
            <div class="flex flex-col gap-2 sm:flex-row">
                <x-search-input placeholder="Search tag, breed, shed…" class="sm:w-60" />
                <x-filter-select model="health" :options="['Healthy', 'Under Observation', 'Under Treatment']" all="Any health status" />
            </div>
        </div>
        <x-table min-width="min-w-[960px]">
            <x-slot:head>
                <tr>
                    <th>Tag ID</th>
                    <th>Breed</th>
                    <th>Sex</th>
                    <th>Age</th>
                    <th class="text-right">Weight</th>
                    <th>Shed</th>
                    <th class="text-right">Milk / day</th>
                    <th>Health</th>
                    <th>Status</th>
                </tr>
            </x-slot:head>
            @foreach ($animals as $animal)
                <tr data-row data-type="{{ $animal['type'] }}" data-health="{{ $animal['health'] }}" data-search="{{ strtolower($animal['tag'].' '.$animal['breed'].' '.$animal['shed'].' '.$animal['type']) }}" x-show="match($el)">
                    <td class="whitespace-nowrap">
                        <p class="font-mono text-sm font-medium text-stone-900">{{ $animal['tag'] }}</p>
                        <p class="text-xs text-stone-500">{{ $animal['type'] }}</p>
                    </td>
                    <td class="whitespace-nowrap">{{ $animal['breed'] }}</td>
                    <td>{{ $animal['sex'] }}</td>
                    <td class="whitespace-nowrap">{{ $animal['age'] }}</td>
                    <td class="text-right whitespace-nowrap tabular-nums">{{ $animal['weight'] }} kg</td>
                    <td class="whitespace-nowrap">{{ $animal['shed'] }}</td>
                    <td class="text-right whitespace-nowrap tabular-nums">{{ $animal['milk'] ? $animal['milk'].' L' : '—' }}</td>
                    <td><x-badge :status="$animal['health']" /></td>
                    <td><x-badge :status="$animal['status']" :dot="false" /></td>
                </tr>
            @endforeach
        </x-table>
        <x-filter-empty />
        <x-slot:footer>
            <p class="text-stone-500">Showing <span class="font-medium text-stone-700" x-text="visible"></span> of {{ count($animals) }} recently updated records · {{ $total }} animals in total</p>
        </x-slot:footer>
    </x-card>

    <x-card title="Health & vaccination schedule" class="mt-6" flush>
        <ul class="divide-y divide-stone-100">
            @foreach ($schedule as $item)
                <li class="flex flex-col gap-2 px-5 py-3.5 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-3">
                        <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-violet-50 text-violet-600"><x-icon name="syringe" class="size-4" /></span>
                        <div>
                            <p class="text-sm font-medium text-stone-900">{{ $item['title'] }}</p>
                            <p class="text-xs text-stone-500">{{ $item['group'] }} · {{ $item['by'] }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 pl-12 sm:pl-0">
                        <span class="inline-flex items-center gap-1 text-sm text-stone-600"><x-icon name="calendar" class="size-4 text-stone-400" /> {{ $item['date'] }}</span>
                        <x-badge :status="$item['status']" />
                    </div>
                </li>
            @endforeach
        </ul>
    </x-card>

    <x-modal name="add-livestock" title="Add livestock" description="Register a new animal in the herd book." submit="Add animal" message="Animal registered">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <x-form.input label="Tag ID" name="tag" required placeholder="e.g. SW-0112" pattern="[A-Za-z]{2}-[0-9]{4}" error="Use the format AB-1234." />
            <x-form.select label="Animal type" name="type" required :options="['Cattle', 'Buffalo', 'Goat', 'Sheep']" />
            <x-form.select label="Breed" name="breed" required :options="['Sahiwal', 'Cholistani', 'Holstein Friesian × Sahiwal', 'Nili-Ravi', 'Beetal', 'Kajli']" />
            <x-form.select label="Sex" name="sex" required :options="['Female', 'Male']" />
            <x-form.input label="Date of birth" name="dob" type="date" required :max="now()->toDateString()" />
            <x-form.input label="Weight" name="weight" type="number" min="1" step="0.5" suffix="kg" />
            <x-form.select label="Shed / pen" name="shed" required :options="['Shed 1', 'Shed 2', 'Shed 3', 'Heifer Shed', 'Fattening Pen', 'Goat House', 'Sheep Pen']" />
            <x-form.select label="Source" name="source" :options="['Born on farm', 'Purchased', 'Transferred']" />
            <div class="sm:col-span-2">
                <x-form.textarea label="Notes" name="notes" placeholder="Dam / sire, purchase price, identifying marks…" />
            </div>
        </div>
    </x-modal>

    <x-modal name="health-record" title="Add health record" size="md" submit="Save record" message="Health record saved">
        <div class="grid grid-cols-1 gap-4">
            <x-form.input label="Animal tag or group" name="animal" required placeholder="e.g. HF-0156 or Shed 2" />
            <x-form.select label="Event type" name="event" required :options="['Vaccination', 'Deworming', 'Treatment', 'Insemination', 'Pregnancy check', 'Calving']" />
            <div class="grid grid-cols-2 gap-4">
                <x-form.input label="Date" name="date" type="date" required :value="now()->toDateString()" />
                <x-form.select label="Veterinarian" name="vet" :options="['Dr. Sana Iqbal', 'External vet']" />
            </div>
            <x-form.textarea label="Diagnosis / medicine" name="notes" />
        </div>
    </x-modal>
</x-layouts.app>
