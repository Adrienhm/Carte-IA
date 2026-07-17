<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Administration</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl shadow-sm p-5">
                    <p class="text-sm text-gray-500">Cartes</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $cardCount }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-5">
                    <p class="text-sm text-gray-500">Packs</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $packCount }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-5">
                    <p class="text-sm text-gray-500">Joueurs</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $userCount }}</p>
                    <p class="text-xs text-red-500 mt-1">{{ $bannedCount }} banni(s)</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-5">
                    <p class="text-sm text-gray-500">Echanges</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $tradeCount }}</p>
                    <p class="text-xs text-amber-500 mt-1">{{ $pendingTrades }} en attente</p>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-5">
                <p class="text-sm text-gray-600">
                    Moteur de generation IA actif :
                    <span class="font-semibold px-2 py-0.5 rounded {{ $aiDriver === 'openai' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">{{ $aiDriver }}</span>
                    @if ($aiDriver === 'fake')
                        <span class="text-gray-400">— mode demo hors-ligne. Renseignez OPENAI_API_KEY et CARD_AI_DRIVER=openai pour la generation reelle.</span>
                    @endif
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="{{ route('admin.cards.index') }}" class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition">
                    <p class="text-2xl mb-2">🃏</p>
                    <p class="font-semibold text-gray-900">Gerer les cartes</p>
                    <p class="text-sm text-gray-500">Creer, generer par IA, editer</p>
                </a>
                <a href="{{ route('admin.card-types.index') }}" class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition">
                    <p class="text-2xl mb-2">🏷️</p>
                    <p class="font-semibold text-gray-900">Types de cartes</p>
                    <p class="text-sm text-gray-500">Categories dynamiques</p>
                </a>
                <a href="{{ route('admin.packs.index') }}" class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition">
                    <p class="text-2xl mb-2">📦</p>
                    <p class="font-semibold text-gray-900">Gerer les packs</p>
                    <p class="text-sm text-gray-500">Composition et probabilites</p>
                </a>
                <a href="{{ route('admin.users.index') }}" class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition">
                    <p class="text-2xl mb-2">👤</p>
                    <p class="font-semibold text-gray-900">Gerer les joueurs</p>
                    <p class="text-sm text-gray-500">Bannir, inventaires</p>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
