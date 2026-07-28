<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Mon inventaire</h2>
            <div class="text-right">
                <p class="text-sm text-gray-500">Valeur totale {{ ($selectedType || $selectedRarity) ? '(filtree)' : '' }}</p>
                <p class="text-xl font-bold text-amber-600">🪙 {{ number_format($totalValue, 0, ',', ' ') }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <form method="GET" class="bg-white rounded-xl shadow-sm p-4 flex flex-wrap items-end gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Type</label>
                    <select name="type" class="rounded-lg border-gray-300 text-sm">
                        <option value="">Tous les types</option>
                        @foreach ($types as $type)
                            <option value="{{ $type->id }}" @selected($selectedType == $type->id)>{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Rarete</label>
                    <select name="rarity" class="rounded-lg border-gray-300 text-sm">
                        <option value="">Toutes les raretes</option>
                        @foreach ($rarities as $rarity)
                            <option value="{{ $rarity->id }}" @selected($selectedRarity == $rarity->id)>{{ $rarity->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-lg text-sm font-medium hover:bg-gray-900">Filtrer</button>
                @if ($selectedType || $selectedRarity)
                    <a href="{{ route('inventory.index') }}" class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700">Reinitialiser</a>
                @endif
                <span class="ms-auto text-sm text-gray-500 self-center">{{ $totalCards }} carte(s)</span>
            </form>

            @if ($grouped->isEmpty())
                <div class="bg-white rounded-xl shadow-sm p-10 text-center text-gray-500">
                    Aucune carte pour ces criteres. <a href="{{ route('packs.index') }}" class="text-purple-600 underline">Ouvrez un pack</a> pour commencer votre collection.
                </div>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                    @foreach ($grouped as $row)
                        <x-game-card :card="$row->card">
                            <div class="mt-2 flex items-center justify-between text-xs">
                                <span class="font-semibold text-gray-700">x{{ $row->qty }}</span>
                                @if ($row->locked_qty > 0)
                                    <span class="text-amber-600">🔒 {{ $row->locked_qty }} bloquee(s)</span>
                                @endif
                            </div>
                        </x-game-card>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
