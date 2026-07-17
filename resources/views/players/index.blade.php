<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Joueurs</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <form method="GET" class="flex gap-3">
                <input type="text" name="search" value="{{ $search }}" placeholder="Rechercher un joueur..."
                       class="flex-1 rounded-lg border-gray-300 text-sm">
                <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-lg text-sm font-medium hover:bg-gray-900">Rechercher</button>
            </form>

            @if ($players->isEmpty())
                <div class="bg-white rounded-xl shadow-sm p-10 text-center text-gray-500">Aucun joueur trouve.</div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($players as $player)
                        <a href="{{ route('players.show', $player) }}" class="bg-white rounded-xl shadow-sm p-5 flex items-center justify-between hover:shadow-md transition">
                            <div class="flex items-center gap-3">
                                <div class="w-11 h-11 rounded-full bg-purple-100 text-purple-700 flex items-center justify-center font-bold">
                                    {{ strtoupper(substr($player->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900">{{ $player->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $player->cards_count }} carte(s)</p>
                                </div>
                            </div>
                            <span class="text-purple-600 text-sm">Voir →</span>
                        </a>
                    @endforeach
                </div>

                {{ $players->links() }}
            @endif
        </div>
    </div>
</x-app-layout>
