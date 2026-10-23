@php
    $collection = collect($employees);
    $departments = $collection->pluck('department')->unique()->values()->all();
    $present = $collection->whereIn('status', ['Present', 'Late'])->count();
@endphp

<x-layouts.app title="Employees" :preview="true" :breadcrumbs="['Employees']">
    <x-page-header description="Staff directory, roles, farm assignments and today’s attendance.">
        <x-slot:actions>
            <x-button variant="secondary" icon="calendar-check" x-on:click="$store.toast.show('Attendance sheet for today marked (demo)', 'info')">Mark attendance</x-button>
            <x-button icon="user-plus" x-on:click="$dispatch('open-modal', 'add-employee')">Add employee</x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card label="Total staff" :value="$collection->count()" icon="users" hint="+ 22 seasonal labourers (cotton picking)" />
        <x-stat-card label="Present today" :value="$present.' / '.$collection->count()" icon="user-round-check" :hint="round($present / $collection->count() * 100).'% attendance'" tone="teal" />
        <x-stat-card label="On leave / absent" :value="$collection->whereIn('status', ['On Leave', 'Absent'])->count()" icon="user-round-x" hint="1 on leave · 1 absent" tone="amber" />
        <x-stat-card label="Monthly payroll" :value="'Rs '.number_format($collection->sum('salary'))" icon="wallet" hint="Permanent & contract staff" tone="violet" />
    </div>

    <div class="mt-6" x-data="filterable({ department: '', type: '' })">
        <div x-data="{ view: 'grid' }">
            <div class="mb-4 flex flex-col gap-3 2xl:flex-row 2xl:items-center 2xl:justify-between">
                <x-segmented model="filters.department" :options="['' => 'All departments'] + array_combine($departments, $departments)" />
                <div class="flex flex-col gap-2 sm:flex-row">
                    <x-search-input placeholder="Search name, role, farm…" class="sm:w-60" />
                    <x-filter-select model="type" :options="['Permanent', 'Contract', 'Seasonal']" all="All contract types" />
                    <div class="flex shrink-0 rounded-lg border border-stone-300 bg-white p-0.5 shadow-xs">
                        <button type="button" x-on:click="view = 'grid'" :class="view === 'grid' ? 'bg-stone-100 text-stone-900' : 'text-stone-400 hover:text-stone-700'" class="flex-1 rounded-md px-2.5 py-1.5" aria-label="Grid view">
                            <x-icon name="layout-grid" class="mx-auto size-4" />
                        </button>
                        <button type="button" x-on:click="view = 'list'" :class="view === 'list' ? 'bg-stone-100 text-stone-900' : 'text-stone-400 hover:text-stone-700'" class="flex-1 rounded-md px-2.5 py-1.5" aria-label="List view">
                            <x-icon name="list" class="mx-auto size-4" />
                        </button>
                    </div>
                </div>
            </div>

            <div x-show="view === 'grid'" class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
                @foreach ($employees as $employee)
                    <article data-row data-department="{{ $employee['department'] }}" data-type="{{ $employee['type'] }}" data-search="{{ strtolower($employee['name'].' '.$employee['role'].' '.$employee['farm']) }}" x-show="match($el)"
                        class="rounded-xl border border-stone-200 bg-white p-5 shadow-card transition-shadow hover:shadow-pop">
                        <div class="flex items-start gap-3">
                            <x-avatar :name="$employee['name']" class="size-11 text-sm!" />
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-semibold text-stone-900">{{ $employee['name'] }}</p>
                                <p class="truncate text-sm text-stone-500">{{ $employee['role'] }}</p>
                            </div>
                            <x-badge :status="$employee['status']" />
                        </div>
                        <dl class="mt-4 space-y-2 text-sm">
                            <div class="flex items-center gap-2 text-stone-600"><x-icon name="map-pin" class="size-4 text-stone-400" /> {{ $employee['farm'] }}</div>
                            <div class="flex items-center gap-2 text-stone-600"><x-icon name="phone" class="size-4 text-stone-400" /> {{ $employee['phone'] }}</div>
                            <div class="flex items-center gap-2 text-stone-600"><x-icon name="briefcase" class="size-4 text-stone-400" /> {{ $employee['department'] }} · {{ $employee['type'] }}</div>
                        </dl>
                        <div class="mt-4 flex items-center justify-between border-t border-stone-100 pt-3 text-xs text-stone-500">
                            <span>Joined {{ $employee['joined'] }}</span>
                            <a href="tel:{{ str_replace(' ', '', $employee['phone']) }}" class="inline-flex items-center gap-1 font-medium text-brand-700 hover:text-brand-800">
                                <x-icon name="phone-call" class="size-3.5" /> Call
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>

            <x-card x-cloak x-show="view === 'list'" flush>
                <x-table>
                    <x-slot:head>
                        <tr>
                            <th>Employee</th>
                            <th>Department</th>
                            <th>Assigned farm</th>
                            <th>Phone</th>
                            <th>Contract</th>
                            <th class="text-right">Salary</th>
                            <th>Today</th>
                        </tr>
                    </x-slot:head>
                    @foreach ($employees as $employee)
                        <tr data-row data-department="{{ $employee['department'] }}" data-type="{{ $employee['type'] }}" data-search="{{ strtolower($employee['name'].' '.$employee['role'].' '.$employee['farm']) }}" x-show="match($el)">
                            <td class="whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <x-avatar :name="$employee['name']" class="size-8" />
                                    <div>
                                        <p class="font-medium text-stone-900">{{ $employee['name'] }}</p>
                                        <p class="text-xs text-stone-500">{{ $employee['role'] }}</p>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $employee['department'] }}</td>
                            <td class="whitespace-nowrap">{{ $employee['farm'] }}</td>
                            <td class="whitespace-nowrap tabular-nums">{{ $employee['phone'] }}</td>
                            <td>{{ $employee['type'] }}</td>
                            <td class="text-right whitespace-nowrap tabular-nums">@money($employee['salary'])</td>
                            <td><x-badge :status="$employee['status']" /></td>
                        </tr>
                    @endforeach
                </x-table>
            </x-card>

            <div class="rounded-xl border border-dashed border-stone-300 bg-white" x-cloak x-show="visible === 0">
                <x-empty-state icon="user-round-search" title="No employees found" description="No staff match your search or filters.">
                    <x-button variant="secondary" size="sm" icon="rotate-ccw" x-on:click="reset()">Clear filters</x-button>
                </x-empty-state>
            </div>
        </div>
    </div>

    <x-modal name="add-employee" title="Add employee" description="Create a staff record and assign a farm." submit="Save employee" message="Employee added">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <x-form.input label="Full name" name="name" required placeholder="e.g. Muhammad Saleem" />
            <x-form.input label="CNIC" name="cnic" required placeholder="35202-1234567-1" pattern="[0-9]{5}-[0-9]{7}-[0-9]" error="Use the format 12345-1234567-1." />
            <x-form.input label="Phone" name="phone" type="tel" required placeholder="+92 300 1234567" />
            <x-form.input label="Email" name="email" type="email" placeholder="optional" />
            <x-form.select label="Role" name="role" required :options="['Farm Manager', 'Field Supervisor', 'Tractor Operator', 'Irrigation Supervisor', 'Herdsman', 'Field Worker', 'Storekeeper', 'Driver', 'Accountant']" />
            <x-form.select label="Department" name="department" required :options="$departments" />
            <x-form.select label="Assigned farm" name="farm" required :options="['Hashmi Main Farm', 'Riverside Farm', 'Canal View Estate', 'Green Valley Dairy', 'All farms']" />
            <x-form.select label="Contract type" name="type" required :options="['Permanent', 'Contract', 'Seasonal']" />
            <x-form.input label="Monthly salary" name="salary" type="number" min="0" step="500" suffix="Rs" />
            <x-form.input label="Joining date" name="joined" type="date" :value="now()->toDateString()" />
        </div>
    </x-modal>
</x-layouts.app>
