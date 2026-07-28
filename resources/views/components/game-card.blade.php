@props(['card', 'locked' => false, 'selectable' => false])

<div
    {{ $attributes->merge(['class' => 'relative bg-white rounded-xl shadow-sm overflow-hidden border-t-4 transition hover:shadow-md']) }}
    style="border-color: {{ $card->rarity->color }}"
>
    @if ($locked)
        <div class="absolute top-2 right-2 z-10 bg-gray-900/80 text-white text-xs px-2 py-1 rounded-full flex items-center gap-1">
            🔒 Bloquee
        </div>
    @endif

    <div class="aspect-square bg-gray-100">
        <img src="{{ $card->imageUrl() }}" alt="{{ $card->name }}" class="w-full h-full object-cover" loading="lazy">
    </div>

    <div class="p-3">
        <div class="flex items-start justify-between gap-2">
            <h3 class="font-semibold text-gray-900 text-sm leading-tight">{{ $card->name }}</h3>
            <x-rarity-badge :rarity="$card->rarity" />
        </div>

        <p class="text-xs text-gray-500 mt-1">{{ $card->cardType->name }}</p>

        <div class="flex items-center justify-between mt-3 text-xs text-gray-600">
            <span title="Puissance">⚔️ {{ $card->power }}</span>
            <span title="Defense">🛡️ {{ $card->defense }}</span>
            <span title="Valeur" class="font-semibold text-amber-600">🪙 {{ $card->value }}</span>
        </div>

        {{ $slot }}
    </div>
</div>
