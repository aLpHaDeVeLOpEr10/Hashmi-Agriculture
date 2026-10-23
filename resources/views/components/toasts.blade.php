<div x-data class="pointer-events-none fixed inset-x-4 bottom-4 z-[60] flex flex-col items-center gap-2 sm:inset-x-auto sm:end-6 sm:bottom-6 sm:items-end" aria-live="polite">
    <template x-for="toast in $store.toast.items" :key="toast.id">
        <div x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
            class="pointer-events-auto flex w-full max-w-sm items-start gap-3 rounded-xl border border-stone-200 bg-white p-3.5 shadow-pop">
            <span class="grid size-7 shrink-0 place-items-center rounded-full"
                :class="toast.type === 'info' ? 'bg-sky-50 text-sky-600' : 'bg-brand-50 text-brand-600'">
                <x-icon name="circle-check" class="size-4" x-show="toast.type !== 'info'" />
                <x-icon name="info" class="size-4" x-show="toast.type === 'info'" />
            </span>
            <p class="flex-1 pt-0.5 text-sm text-stone-700" x-text="toast.message"></p>
            <button type="button" x-on:click="$store.toast.dismiss(toast.id)" class="rounded-md p-0.5 text-stone-400 hover:text-stone-600" aria-label="Dismiss">
                <x-icon name="x" class="size-4" />
            </button>
        </div>
    </template>
</div>
