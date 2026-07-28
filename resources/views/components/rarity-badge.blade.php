@props(['rarity'])

<span
    {{ $attributes->merge(['class' => 'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold text-white']) }}
    style="background-color: {{ $rarity->color }}"
>
    {{ $rarity->name }}
</span>
