<div x-data class="space-y-3 empty:hidden" x-cloak x-show="!$store.offline.online || $store.offline.queue.length > 0">
    <div x-show="!$store.offline.online" class="mb-6 flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900" role="status">
        <x-icon name="wifi-off" class="mt-0.5 size-5 shrink-0 text-amber-600" />
        <p><span class="font-semibold">{{ __('You are offline.') }}</span> {{ __('Expenses you add are saved on this phone and sync when the connection returns.') }}</p>
    </div>

    <div x-show="$store.offline.pending > 0" class="mb-6 flex flex-wrap items-center gap-3 rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-900" role="status">
        <x-icon name="cloud-upload" class="size-5 shrink-0 text-sky-600" />
        <p class="flex-1"><span class="font-semibold" x-text="$store.offline.pending"></span> {{ __('expense(s) saved offline, waiting to sync.') }}</p>
        <x-button size="sm" variant="secondary" icon="refresh-cw" x-on:click="$store.offline.sync()" x-bind:disabled="!$store.offline.online || $store.offline.syncing">{{ __('Sync now') }}</x-button>
    </div>

    <template x-for="entry in $store.offline.failed" :key="entry.uuid">
        <div class="mb-6 flex flex-wrap items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900" role="alert">
            <x-icon name="circle-alert" class="mt-0.5 size-5 shrink-0 text-red-600" />
            <div class="min-w-0 flex-1">
                <p><span class="font-semibold">{{ __('Could not sync') }}</span> “<span x-text="entry.label"></span>”</p>
                <p class="mt-0.5 text-red-700" x-text="entry.errors.join(' ')"></p>
            </div>
            <x-button size="sm" variant="secondary" x-on:click="$store.offline.discard(entry.uuid)">{{ __('Discard') }}</x-button>
        </div>
    </template>
</div>
