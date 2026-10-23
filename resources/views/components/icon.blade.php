@props(['name'])

{{ svg('lucide-'.$name, $attributes->get('class', 'size-5'), ['aria-hidden' => 'true', 'stroke-width' => $attributes->get('stroke', 1.75)]) }}
