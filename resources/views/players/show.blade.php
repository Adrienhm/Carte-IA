<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Profil de {{ $player->name }}</h2>
            @unless ($isSelf)
                <a href="{{ route('trades.create', $player) }}" class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg text-sm font-medium hover:bg-purple-700">
                    Proposer un echange
                </a>
            @endunless
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white rounded-xl shadow-sm p-6 flex flex-wrap items-center gap-8">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-full bg-purple-100 text-purple-700 flex items-center justify-center text-2xl font-bold">
                        {{ strtoupper(substr($player->name, 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-lg font-bold text-gray-900">{{ $player->name }}</p>
                        <p class="text-sm text-gray-500">Membre depuis {{ $player->created_at->format('d/m/Y') }}</p>
                    </div>
                </div>
                <div class="flex gap-8 ms-auto">
                    <div class="text-center">
                        <p class="text-2xl font-bold text-gray-900">{{ $totalCards }}</p>
                        <p class="text-xs text-gray-500">Cartes</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-amber-600">🪙 {{ number_format($collectionValue, 0, ',', ' ') }}</p>
                        <p class="text-xs text-gray-500">Valeur</p>
                    </div>
                </div>
            </div>

            @if ($grouped->isEmpty())
                <div class="bg-white rounded-xl shadow-sm p-10 text-center text-gray-500">Ce joueur n'a pas encore de cartes.</div>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                    @foreach ($grouped as $row)
                        <x-game-card :card="$row->card">
                            <p class="mt-2 text-xs font-semibold text-gray-700">x{{ $row->qty }}</p>
                        </x-game-card>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
