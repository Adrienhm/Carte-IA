{{--
    Formulaire de pack avec composition. Chaque carte peut recevoir un poids ;
    poids 0 (ou vide) = carte absente du pack. Un bloc Alpine recalcule en
    direct la probabilite de chaque carte (poids / somme des poids), ce qui
    rend l'equilibrage lisible pour l'administrateur (CDC 4.3).
--}}
@php
    $assigned = $assigned ?? [];
    $cardsData = $cards->map(fn ($c) => [
        'id' => $c->id,
        'weight' => (int) ($assigned[$c->id] ?? 0),
    ])->values();
@endphp

<div
    x-data="{
        weights: {{ Illuminate\Support\Js::from($cardsData->pluck('weight', 'id')) }},
        total() { return Object.values(this.weights).reduce((s, w) => s + (parseInt(w) || 0), 0); },
        prob(id) {
            const t = this.total();
            if (!t) return '0';
            return ((parseInt(this.weights[id]) || 0) / t * 100).toFixed(1);
        }
    }"
    class="space-y-6"
>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
            <input type="text" name="name" value="{{ old('name', $pack->name) }}" required class="w-full rounded-lg border-gray-300 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Cartes par ouverture</label>
            <input type="number" name="cards_per_pack" value="{{ old('cards_per_pack', $pack->cards_per_pack) }}" min="1" max="20" required class="w-full rounded-lg border-gray-300 text-sm">
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
        <textarea name="description" rows="2" class="w-full rounded-lg border-gray-300 text-sm">{{ old('description', $pack->description) }}</textarea>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Image (optionnel)</label>
        @if ($pack->image_path)
            <img src="{{ $pack->imageUrl() }}" class="w-24 h-16 rounded object-cover mb-2 bg-gray-100" alt="">
        @endif
        <input type="file" name="image" accept="image/*" class="text-sm">
    </div>

    <label class="flex items-center gap-2">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $pack->is_active)) class="rounded border-gray-300 text-purple-600">
        <span class="text-sm text-gray-700">Pack actif</span>
    </label>

    <div>
        <div class="flex items-center justify-between mb-2">
            <h3 class="font-semibold text-gray-800">Composition & probabilites</h3>
            <span class="text-sm text-gray-500">Somme des poids : <span class="font-bold" x-text="total()"></span></span>
        </div>
        <p class="text-xs text-gray-400 mb-3">Poids 0 = carte exclue du pack. La probabilite est le poids divise par la somme des poids.</p>

        <div class="border border-gray-200 rounded-lg divide-y divide-gray-100 max-h-[28rem] overflow-y-auto">
            @foreach ($cards as $card)
                <div class="flex items-center gap-3 px-3 py-2 hover:bg-gray-50">
                    <img src="{{ $card->imageUrl() }}" class="w-9 h-9 rounded object-cover bg-gray-100" alt="">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $card->name }}</p>
                        <p class="text-xs text-gray-500">{{ $card->cardType->name }} · {{ $card->rarity->name }}</p>
                    </div>
                    <div class="w-24 text-right">
                        <span class="text-xs text-gray-500">Proba : <span class="font-semibold text-purple-600" x-text="prob({{ $card->id }}) + '%'"></span></span>
                    </div>
                    <input
                        type="number"
                        name="weights[{{ $card->id }}]"
                        min="0"
                        x-model="weights[{{ $card->id }}]"
                        value="{{ old('weights.'.$card->id, $assigned[$card->id] ?? 0) }}"
                        class="w-20 rounded-lg border-gray-300 text-sm text-right"
                    >
                </div>
            @endforeach
        </div>
    </div>
</div>
