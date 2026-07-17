<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tableau de bord</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl shadow-sm p-5">
                    <p class="text-sm text-gray-500">Packs a ouvrir</p>
                    <p class="text-3xl font-bold text-purple-600">{{ $packsOwned }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-5">
                    <p class="text-sm text-gray-500">Cartes possedees</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $cardsOwned }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-5">
                    <p class="text-sm text-gray-500">Valeur de la collection</p>
                    <p class="text-3xl font-bold text-amber-600">🪙 {{ number_format($collectionValue, 0, ',', ' ') }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-5">
                    <p class="text-sm text-gray-500">Echanges en attente</p>
                    <p class="text-3xl font-bold text-red-500">{{ $pendingReceived }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $pendingSent }} envoye(s)</p>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="font-semibold text-gray-800 mb-4">Ma collection par rarete</h3>
                <div class="space-y-3">
                    @php($max = max($byRarity->max('owned_count'), 1))
                    @foreach ($byRarity as $rarity)
                        <div class="flex items-center gap-3">
                            <x-rarity-badge :rarity="$rarity" class="w-28 justify-center" />
                            <div class="flex-1 bg-gray-100 rounded-full h-3 overflow-hidden">
                                <div class="h-3 rounded-full" style="width: {{ ($rarity->owned_count / $max) * 100 }}%; background-color: {{ $rarity->color }}"></div>
                            </div>
                            <span class="w-10 text-right text-sm font-medium text-gray-700">{{ $rarity->owned_count }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('packs.index') }}" class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg font-medium hover:bg-purple-700">Ouvrir des packs</a>
                <a href="{{ route('inventory.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50">Voir mon inventaire</a>
                <a href="{{ route('players.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50">Trouver un joueur</a>
            </div>
        </div>
    </div>
</x-app-layout>
