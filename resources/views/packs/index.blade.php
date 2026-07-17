<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Packs</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Reveal des cartes tout juste obtenues (CDC bonus animation) --}}
            @if ($revealed->isNotEmpty())
                <div
                    x-data="{ shown: 0, total: {{ $revealed->count() }} }"
                    x-init="const t = setInterval(() => { shown++; if (shown >= total) clearInterval(t); }, 450)"
                    class="bg-gradient-to-br from-purple-700 to-indigo-800 rounded-2xl shadow-lg p-6 text-white"
                >
                    <h3 class="text-lg font-bold mb-1">✨ {{ $openedPack }} ouvert !</h3>
                    <p class="text-purple-200 text-sm mb-5">Vos {{ $revealed->count() }} nouvelles cartes :</p>

                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                        @foreach ($revealed as $i => $card)
                            <div
                                x-show="shown > {{ $i }}"
                                x-transition:enter="transition ease-out duration-500"
                                x-transition:enter-start="opacity-0 translate-y-4 scale-90"
                                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                            >
                                <x-game-card :card="$card" />
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-5">
                        <a href="{{ route('inventory.index') }}" class="text-sm underline text-purple-100 hover:text-white">Voir mon inventaire →</a>
                    </div>
                </div>
            @endif

            <p class="text-gray-600 text-sm">
                Vous ouvrez les packs que vous possedez. Chaque ouverture tire des cartes au hasard
                selon les probabilites configurees (tirage effectue cote serveur).
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($packs as $pack)
                    @php($qty = $owned[$pack->id] ?? 0)
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden flex flex-col">
                        <div class="aspect-video bg-gray-100">
                            <img src="{{ $pack->imageUrl() }}" alt="{{ $pack->name }}" class="w-full h-full object-cover">
                        </div>
                        <div class="p-5 flex-1 flex flex-col">
                            <div class="flex items-center justify-between">
                                <h3 class="font-bold text-gray-900">{{ $pack->name }}</h3>
                                <span class="text-xs font-medium px-2 py-1 rounded-full {{ $qty > 0 ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-500' }}">
                                    x{{ $qty }} en stock
                                </span>
                            </div>
                            <p class="text-sm text-gray-500 mt-2 flex-1">{{ $pack->description }}</p>
                            <p class="text-xs text-gray-400 mt-2">{{ $pack->cards_per_pack }} cartes par pack</p>

                            <form method="POST" action="{{ route('packs.open', $pack) }}" class="mt-4">
                                @csrf
                                <button
                                    type="submit"
                                    @disabled($qty < 1)
                                    class="w-full px-4 py-2 rounded-lg font-medium text-white transition {{ $qty > 0 ? 'bg-purple-600 hover:bg-purple-700' : 'bg-gray-300 cursor-not-allowed' }}"
                                >
                                    {{ $qty > 0 ? 'Ouvrir un pack' : 'Aucun pack disponible' }}
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
