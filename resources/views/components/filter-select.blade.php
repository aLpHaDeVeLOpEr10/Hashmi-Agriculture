@props(['model', 'options', 'all' => 'All'])

<select x-model="filters.{{ $model }}" aria-label="{{ $all }}" {{ $attributes->class(['form-control h-9 w-full py-0 pe-8 sm:w-auto']) }}>
    <option value="">{{ $all }}</option>
    @foreach ($options as $option)
        <option value="{{ $option }}">{{ $option }}</option>
    @endforeach
</select>
