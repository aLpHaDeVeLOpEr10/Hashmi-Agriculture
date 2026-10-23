@props(['name'])

@php
    $initials = collect(explode(' ', $name))->filter()->take(2)->map(fn ($part) => mb_substr($part, 0, 1))->implode('');
    $tones = ['bg-brand-100 text-brand-800', 'bg-amber-100 text-amber-800', 'bg-sky-100 text-sky-800', 'bg-violet-100 text-violet-800', 'bg-rose-100 text-rose-800', 'bg-teal-100 text-teal-800', 'bg-orange-100 text-orange-800'];
    $tone = $tones[crc32($name) % count($tones)];
@endphp

<span {{ $attributes->class(['inline-grid shrink-0 place-items-center rounded-full text-xs font-semibold select-none', $tone]) }} aria-hidden="true">{{ strtoupper($initials) }}</span>
