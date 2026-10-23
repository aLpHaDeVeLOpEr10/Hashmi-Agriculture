@props(['label', 'name', 'options' => [], 'required' => false, 'placeholder' => null, 'selected' => null, 'hint' => null])

@php
    $key = str_replace(['[', ']'], ['.', ''], $name);
    $id = 'f-'.\Illuminate\Support\Str::slug($key).'-'.substr(md5($label), 0, 5);
    $options = array_is_list($options) ? array_combine($options, $options) : $options;
    $serverError = $errors->first($key);
    $placeholder ??= __('Select…');
@endphp

<div class="field">
    <label for="{{ $id }}" class="mb-1.5 block text-sm font-medium text-stone-700">
        {{ $label }} @if ($required)<span class="text-red-500">*</span>@endif
    </label>
    <select id="{{ $id }}" name="{{ $name }}" @required($required) @if ($serverError) aria-invalid="true" @endif {{ $attributes->class(['form-control']) }}>
        @if ($placeholder !== false)
            <option value="">{{ $placeholder }}</option>
        @endif
        @if ($slot->isNotEmpty())
            {{ $slot }}
        @else
            @foreach ($options as $value => $text)
                <option value="{{ $value }}" @selected((string) $selected === (string) $value)>{{ $text }}</option>
            @endforeach
        @endif
    </select>
    @if ($hint)
        <p class="mt-1.5 text-xs text-stone-500">{{ $hint }}</p>
    @endif
    @if ($serverError)
        <p class="mt-1.5 text-xs text-red-600">{{ $serverError }}</p>
    @else
        <p class="field-error">{{ __('Please choose an option.') }}</p>
    @endif
</div>
