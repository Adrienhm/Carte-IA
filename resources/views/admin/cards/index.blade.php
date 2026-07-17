<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Cartes</h2>
            <a href="{{ route('admin.cards.create') }}" class="px-4 py-2 bg-purple-600 text-white rounded-lg text-sm font-medium hover:bg-purple-700">+ Nouvelle carte</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Generation IA rapide (CDC 5.1) --}}
            <div class="bg-white rounded-xl shadow-sm p-5">
                <h3 class="font-semibold text-gray-800 mb-3">⚡ Generer une carte par IA</h3>
                <form method="POST" action="{{ route('admin.cards.generate') }}" class="flex flex-wrap items-end gap-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Type</label>
                        <select name="card_type_id" required class="rounded-lg border-gray-300 text-sm">
                            @foreach ($types as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Rarete</label>
                        <select name="rarity_id" required class="rounded-lg border-gray-300 text-sm">
                            @foreach ($rarities as $rarity)
                                <option value="{{ $rarity->id }}">{{ $rarity->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nom (optionnel)</label>
                        <input type="text" name="name" class="rounded-lg border-gray-300 text-sm" placeholder="Laisser vide = IA">
                    </div>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">Generer</button>
                </form>
            </div>

            {{-- Filtres --}}
            <form method="GET" class="flex flex-wrap gap-3">
                <select name="type" class="rounded-lg border-gray-300 text-sm" onchange="this.form.submit()">
                    <option value="">Tous les types</option>
                    @foreach ($types as $type)
                        <option value="{{ $type->id }}" @selected(request('type') == $type->id)>{{ $type->name }}</option>
                    @endforeach
                </select>
                <select name="rarity" class="rounded-lg border-gray-300 text-sm" onchange="this.form.submit()">
                    <option value="">Toutes les raretes</option>
                    @foreach ($rarities as $rarity)
                        <option value="{{ $rarity->id }}" @selected(request('rarity') == $rarity->id)>{{ $rarity->name }}</option>
                    @endforeach
                </select>
            </form>

            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-gray-500">
                            <th class="px-4 py-3">Carte</th>
                            <th class="px-4 py-3">Type</th>
                            <th class="px-4 py-3">Rarete</th>
                            <th class="px-4 py-3">Stats</th>
                            <th class="px-4 py-3">Valeur</th>
                            <th class="px-4 py-3">Statut</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($cards as $card)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $card->imageUrl() }}" class="w-10 h-10 rounded object-cover bg-gray-100" alt="">
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $card->name }}</p>
                                            @if ($card->is_ai_generated)<span class="text-xs text-indigo-500">IA</span>@endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-gray-600">{{ $card->cardType->name }}</td>
                                <td class="px-4 py-3"><x-rarity-badge :rarity="$card->rarity" /></td>
                                <td class="px-4 py-3 text-gray-600">⚔️{{ $card->power }} 🛡️{{ $card->defense }}</td>
                                <td class="px-4 py-3 text-amber-600 font-medium">🪙 {{ $card->value }}</td>
                                <td class="px-4 py-3">
                                    @if ($card->is_active)
                                        <span class="text-xs text-green-600">Active</span>
                                    @else
                                        <span class="text-xs text-gray-400">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <a href="{{ route('admin.cards.edit', $card) }}" class="text-purple-600 hover:underline">Editer</a>
                                    <form method="POST" action="{{ route('admin.cards.destroy', $card) }}" class="inline" onsubmit="return confirm('Supprimer cette carte ?')">
                                        @csrf @method('DELETE')
                                        <button class="text-red-500 hover:underline ms-2">Suppr.</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $cards->links() }}
        </div>
    </div>
</x-app-layout>
