@use('App\Models\User')

<x-settings-layout :title="__('Users & roles')" :description="__('Owner sees everything and reopens closed seasons. Manager enters and edits, records payments and closes seasons. Data entry adds expenses and production records only. Viewer reads reports.')"
    :record="['name' => '', 'phone' => '', 'role' => 'data_entry', 'password' => '', 'is_active' => true]">
    <x-slot:actions>
        <x-button icon="user-plus" size="sm" x-on:click="record = { ...blank }; $dispatch('open-modal', 'record')">{{ __('Add user') }}</x-button>
    </x-slot:actions>

    <x-table min-width="min-w-[560px]">
        <x-slot:head>
            <tr><th>{{ __('Name') }}</th><th>{{ __('Mobile') }}</th><th>{{ __('Role') }}</th><th>{{ __('Status') }}</th><th></th></tr>
        </x-slot:head>
        @foreach ($users as $member)
            <tr>
                <td>
                    <div class="flex items-center gap-3">
                        <x-avatar :name="$member->name" class="size-8" />
                        <span class="font-medium text-stone-900">{{ $member->name }}</span>
                    </div>
                </td>
                <td class="whitespace-nowrap tabular-nums" dir="ltr">{{ \App\Support\Phone::display($member->phone) }}</td>
                <td>{{ User::roleLabel($member->role) }}</td>
                <td><x-badge :status="$member->is_active ? 'Active' : 'Inactive'">{{ $member->is_active ? __('Active') : __('Inactive') }}</x-badge></td>
                <td class="text-end">
                    <x-button variant="ghost" size="sm" icon="pencil" x-on:click="record = {{ Js::from($member->only('id', 'name', 'phone', 'role', 'is_active') + ['password' => '']) }}; $dispatch('open-modal', 'record')">{{ __('Edit') }}</x-button>
                </td>
            </tr>
        @endforeach
    </x-table>

    <x-slot:modals>
        <x-modal name="record" :title="__('Add user')" title-bind="record.id ? {{ Js::from(__('Edit user')) }} : {{ Js::from(__('Add user')) }}" size="md"
            bind-action="record.id ? {{ Js::from(url('/settings/users')) }} + '/' + record.id : {{ Js::from(url('/settings/users')) }}">
        <x-slot:hidden>
            <input type="hidden" name="_method" :value="record.id ? 'PUT' : 'POST'">
            <input type="hidden" name="_record" :value="JSON.stringify({ id: record.id ?? null })">
        </x-slot:hidden>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-form.input :label="__('Name')" name="name" required maxlength="100" x-model="record.name" />
                <x-form.input :label="__('Mobile number')" name="phone" type="tel" x-model="record.phone" x-bind:disabled="!!record.id" x-bind:required="!record.id" dir="ltr" placeholder="0300 1234567" />
                <x-form.select :label="__('Role')" name="role" required :placeholder="false" x-model="record.role">
                    @foreach (User::ROLES as $role)
                        <option value="{{ $role }}">{{ User::roleLabel($role) }}</option>
                    @endforeach
                </x-form.select>
                <x-form.input :label="__('Password')" name="password" type="password" minlength="8" autocomplete="new-password" x-model="record.password" x-bind:required="!record.id"
                    :hint="__('At least 8 characters. Leave blank to keep the current password.')" :error="__('At least 8 characters.')" />
            </div>
            <label class="mt-4 flex items-center gap-2 text-sm text-stone-700" x-show="record.id">
                <input type="checkbox" name="is_active" value="1" x-model="record.is_active" class="size-4 rounded border-stone-300 text-brand-600 focus:ring-brand-500">
                {{ __('Can sign in') }}
            </label>
        </x-modal>
    </x-slot:modals>
</x-settings-layout>
