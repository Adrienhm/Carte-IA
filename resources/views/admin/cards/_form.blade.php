@php($card = $card ?? new \App\Models\Card(['is_active' => true, 'power' => 0, 'defense' => 0, 'value' => 0]))

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
        <input type="text" name="name" value="{{ old('name', $card->name) }}" required class="w-full rounded-lg border-gray-300 text-sm">
    </div>
    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
            <select name="card_type_id" required class="w-full rounded-lg border-gray-300 text-sm">
                @foreach ($types as $type)
                    <option value="{{ $type->id }}" @selected(old('card_type_id', $card->card_type_id) == $type->id)>{{ $type->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Rarete</label>
            <select name="rarity_id" required class="w-full rounded-lg border-gray-300 text-sm">
                @foreach ($rarities as $rarity)
                    <option value="{{ $rarity->id }}" @selected(old('rarity_id', $card->rarity_id) == $rarity->id)>{{ $rarity->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
    <textarea name="description" rows="2" class="w-full rounded-lg border-gray-300 text-sm">{{ old('description', $card->description) }}</textarea>
</div>

<div class="grid grid-cols-3 gap-3">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Puissance</label>
        <input type="number" name="power" value="{{ old('power', $card->power) }}" min="0" max="65535" required class="w-full rounded-lg border-gray-300 text-sm">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Defense</label>
        <input type="number" name="defense" value="{{ old('defense', $card->defense) }}" min="0" max="65535" required class="w-full rounded-lg border-gray-300 text-sm">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Valeur 🪙</label>
        <input type="number" name="value" value="{{ old('value', $card->value) }}" min="0" required class="w-full rounded-lg border-gray-300 text-sm">
    </div>
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Image (optionnel)</label>
    @if ($card->image_path)
        <img src="{{ $card->imageUrl() }}" class="w-24 h-24 rounded object-cover mb-2 bg-gray-100" alt="">
    @endif
    <input type="file" name="image" accept="image/*" class="text-sm">
    <p class="text-xs text-gray-400 mt-1">Formats image, 4 Mo max.</p>
</div>

<label class="flex items-center gap-2">
    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $card->is_active)) class="rounded border-gray-300 text-purple-600">
    <span class="text-sm text-gray-700">Carte active (disponible dans les packs)</span>
</label>
