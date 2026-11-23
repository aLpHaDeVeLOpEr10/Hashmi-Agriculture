<x-layouts.app :title="__('My profile')" :breadcrumbs="[__('My profile')]">
    <div class="mx-auto max-w-3xl space-y-6">
        <x-card>
            <div class="flex items-center gap-4">
                <x-avatar :name="$user->name" class="size-14" />
                <div class="min-w-0">
                    <p class="truncate text-lg font-semibold text-stone-900">{{ $user->name }}</p>
                    <p class="text-sm text-stone-500">
                        {{ \App\Models\User::roleLabel($user->role) }} ·
                        <span dir="ltr" class="tabular-nums">{{ \App\Support\Phone::display($user->phone) }}</span>
                    </p>
                </div>
            </div>
        </x-card>

        <form method="POST" action="{{ route('profile.update') }}" novalidate x-data="validatedForm" x-on:submit="submit($event)">
            @csrf
            @method('PUT')
            <x-card :title="__('Profile information')" :description="__('Your mobile number is what you use to sign in.')">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <x-form.input :label="__('Name')" name="name" required maxlength="100" autocomplete="name" :value="old('name', $user->name)" />
                    <x-form.input :label="__('Mobile number')" name="phone" type="tel" required dir="ltr" autocomplete="tel" placeholder="0300 1234567" :value="old('phone', $user->phone)" />
                </div>
                <x-slot:footer>
                    <div class="flex justify-end">
                        <x-button type="submit" icon="check">{{ __('Save profile') }}</x-button>
                    </div>
                </x-slot:footer>
            </x-card>
        </form>

        <form method="POST" action="{{ route('profile.password') }}" novalidate x-data="validatedForm" x-on:submit="submit($event)">
            @csrf
            @method('PUT')
            <x-card :title="__('Change password')" :description="__('Enter your current password, then choose a new one.')">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2 sm:max-w-[calc(50%-0.5rem)]">
                        <x-form.input :label="__('Current password')" name="current_password" type="password" required autocomplete="current-password" />
                    </div>
                    <x-form.input :label="__('New password')" name="password" type="password" required minlength="8" autocomplete="new-password"
                        :hint="__('At least 8 characters.')" :error="__('At least 8 characters.')" />
                    <x-form.input :label="__('Confirm new password')" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password" />
                </div>
                <x-slot:footer>
                    <div class="flex justify-end">
                        <x-button type="submit" icon="key-round">{{ __('Change password') }}</x-button>
                    </div>
                </x-slot:footer>
            </x-card>
        </form>
    </div>
</x-layouts.app>
