@props(['label', 'name', 'type' => 'text', 'required' => false, 'hint' => null, 'suffix' => null, 'error' => null])

@php
    $key = str_replace(['[', ']'], ['.', ''], $name);
    $id = 'f-'.\Illuminate\Support\Str::slug($key).'-'.substr(md5($label), 0, 5);
    $serverError = $errors->first($key);
@endphp

<div class="field">
    <label for="{{ $id }}" class="mb-1.5 block text-sm font-medium text-stone-700">
        {{ $label }} @if ($required)<span class="text-red-500">*</span>@endif
    </label>
    <div class="relative">
        <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}" @required($required) @if ($serverError) aria-invalid="true" @endif
            {{ $attributes->class(['form-control', 'pe-16' => $suffix]) }}>
        @if ($suffix)
            <span class="pointer-events-none absolute inset-y-0 end-3 flex items-center text-sm text-stone-400">{{ $suffix }}</span>
        @endif
    </div>
    @if ($hint)
        <p class="mt-1.5 text-xs text-stone-500">{{ $hint }}</p>
    @endif
    @if ($serverError)
        <p class="mt-1.5 text-xs text-red-600">{{ $serverError }}</p>
    @else
        <p class="field-error">{{ $error ?? ($type === 'email' ? __('Enter a valid email address.') : __('Please fill in this field.')) }}</p>
    @endif
</div>
