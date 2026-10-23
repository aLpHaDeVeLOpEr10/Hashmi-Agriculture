@props(['label', 'name', 'required' => false, 'rows' => 3])

@php
    $id = 'f-'.\Illuminate\Support\Str::slug($name).'-'.substr(md5($label), 0, 5);
    $serverError = $errors->first($name);
@endphp

<div class="field">
    <label for="{{ $id }}" class="mb-1.5 block text-sm font-medium text-stone-700">
        {{ $label }} @if ($required)<span class="text-red-500">*</span>@endif
    </label>
    <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $rows }}" @required($required) @if ($serverError) aria-invalid="true" @endif {{ $attributes->class(['form-control']) }}>{{ $slot }}</textarea>
    @if ($serverError)
        <p class="mt-1.5 text-xs text-red-600">{{ $serverError }}</p>
    @else
        <p class="field-error">{{ __('Please fill in this field.') }}</p>
    @endif
</div>
