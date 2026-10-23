@props(['name', 'title', 'description' => null, 'size' => 'lg', 'submit' => null, 'message' => null, 'action' => null, 'method' => 'POST', 'bindAction' => null, 'titleBind' => null, 'show' => false, 'files' => false])

@php
    $width = ['sm' => 'sm:max-w-md', 'md' => 'sm:max-w-lg', 'lg' => 'sm:max-w-2xl', 'xl' => 'sm:max-w-4xl'][$size];
    $isForm = $action || $bindAction;
@endphp

<div x-data="{ open: @js((bool) $show) }" x-cloak x-show="open"
    x-on:open-modal.window="if ($event.detail === @js($name)) open = true"
    x-on:close-modal.window="open = false"
    x-on:keydown.escape.window="open = false"
    class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="modal-{{ $name }}-title">

    <div x-show="open" x-transition.opacity.duration.150ms class="fixed inset-0 bg-stone-900/40 backdrop-blur-[2px]" x-on:click="open = false"></div>

    <div class="relative flex min-h-full items-end justify-center p-0 sm:items-center sm:p-6">
        <div x-show="open" x-trap.noscroll.inert="open"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="relative w-full {{ $width }} rounded-t-2xl bg-white shadow-pop sm:rounded-2xl">

            <div class="flex items-start justify-between gap-4 border-b border-stone-100 px-5 py-4 sm:px-6">
                <div>
                    <h2 id="modal-{{ $name }}-title" class="text-base font-semibold text-stone-900" @if ($titleBind) x-text="{{ $titleBind }}" @endif>{{ $title }}</h2>
                    @if ($description)
                        <p class="mt-0.5 text-sm text-stone-500">{{ $description }}</p>
                    @endif
                </div>
                <button type="button" x-on:click="open = false" class="-me-1.5 rounded-lg p-1.5 text-stone-400 hover:bg-stone-100 hover:text-stone-600" aria-label="{{ __('Close') }}">
                    <x-icon name="x" class="size-5" />
                </button>
            </div>

            @if ($isForm)
                <form method="POST" @if ($action) action="{{ $action }}" @endif @if ($bindAction) :action="{{ $bindAction }}" @endif
                    @if ($files) enctype="multipart/form-data" @endif
                    x-data="validatedForm" x-on:submit="submit($event)" novalidate>
                    @csrf
                    @if (strtoupper($method) !== 'POST')
                        @method($method)
                    @endif
                    {{ $hidden ?? '' }}
                    <div class="max-h-[70vh] overflow-y-auto px-5 py-5 sm:px-6">{{ $slot }}</div>
                    <div class="flex flex-col-reverse gap-2 border-t border-stone-100 bg-stone-50/60 px-5 py-3.5 sm:flex-row sm:justify-end sm:rounded-b-2xl sm:px-6">
                        <x-button variant="secondary" x-on:click="open = false">{{ __('Cancel') }}</x-button>
                        <x-button type="submit" icon="check" x-bind:disabled="busy">{{ $submit ?? __('Save') }}</x-button>
                    </div>
                </form>
            @elseif ($submit)
                <form x-data="demoForm(@js($message ?? __('Saved')))" x-on:submit.prevent="submit($event)" novalidate>
                    <div class="max-h-[70vh] overflow-y-auto px-5 py-5 sm:px-6">{{ $slot }}</div>
                    <div class="flex flex-col-reverse gap-2 border-t border-stone-100 bg-stone-50/60 px-5 py-3.5 sm:flex-row sm:justify-end sm:rounded-b-2xl sm:px-6">
                        <x-button variant="secondary" x-on:click="open = false">{{ __('Cancel') }}</x-button>
                        <x-button type="submit" icon="check">{{ $submit }}</x-button>
                    </div>
                </form>
            @else
                <div class="max-h-[75vh] overflow-y-auto px-5 py-5 sm:px-6">{{ $slot }}</div>
                @isset($footer)
                    <div class="flex flex-col-reverse gap-2 border-t border-stone-100 bg-stone-50/60 px-5 py-3.5 sm:flex-row sm:justify-end sm:rounded-b-2xl sm:px-6">{{ $footer }}</div>
                @endisset
            @endif
        </div>
    </div>
</div>
