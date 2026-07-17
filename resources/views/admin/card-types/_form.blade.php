@php($type = $type ?? new \App\Models\CardType(['is_active' => true]))

<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
    <input type="text" name="name" value="{{ old('name', $type->name) }}" required class="w-full rounded-lg border-gray-300 text-sm">
</div>
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
    <input type="text" name="description" value="{{ old('description', $type->description) }}" class="w-full rounded-lg border-gray-300 text-sm">
</div>
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Indice de prompt IA</label>
    <input type="text" name="prompt_hint" value="{{ old('prompt_hint', $type->prompt_hint) }}" class="w-full rounded-lg border-gray-300 text-sm" placeholder="ex: armored warrior holding a weapon">
    <p class="text-xs text-gray-400 mt-1">Injecte dans le prompt envoye a l'IA pour ce type de carte.</p>
</div>
<label class="flex items-center gap-2">
    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $type->is_active)) class="rounded border-gray-300 text-purple-600">
    <span class="text-sm text-gray-700">Type actif</span>
</label>
