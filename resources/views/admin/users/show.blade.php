<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Joueur : {{ $user->name }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white rounded-xl shadow-sm p-5 flex flex-wrap items-center gap-6">
                <div>
                    <p class="font-bold text-gray-900">{{ $user->name }}</p>
                    <p class="text-sm text-gray-500">{{ $user->email }}</p>
                    @if ($user->isBanned())
                        <p class="text-xs text-red-600 mt-1">Banni le {{ $user->banned_at->format('d/m/Y') }} @if($user->ban_reason)— {{ $user->ban_reason }}@endif</p>
                    @endif
                </div>
                <div class="flex gap-6 text-center">
                    <div><p class="text-xl font-bold">{{ $cards->count() }}</p><p class="text-xs text-gray-500">Cartes</p></div>
                    <div><p class="text-xl font-bold text-amber-600">🪙 {{ number_format($collectionValue, 0, ',', ' ') }}</p><p class="text-xs text-gray-500">Valeur</p></div>
                    <div><p class="text-xl font-bold">{{ $packsOwned }}</p><p class="text-xs text-gray-500">Packs</p></div>
                </div>
                <div class="ms-auto">
                    @unless ($user->isAdmin())
                        @if ($user->isBanned())
                            <form method="POST" action="{{ route('admin.users.unban', $user) }}">
                                @csrf
                                <button class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700">Debannir</button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('admin.users.ban', $user) }}" class="flex gap-2" onsubmit="return confirm('Bannir ce joueur ?')">
                                @csrf
                                <input type="text" name="reason" placeholder="Motif (optionnel)" class="rounded-lg border-gray-300 text-sm">
                                <button class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700">Bannir</button>
                            </form>
                        @endif
                    @endunless
                </div>
            </div>

            {{-- Attribuer un pack --}}
            <div class="bg-white rounded-xl shadow-sm p-5">
                <h3 class="font-semibold text-gray-800 mb-3">Attribuer des packs</h3>
                <form method="POST" action="{{ route('admin.users.grant-pack', $user) }}" class="flex flex-wrap items-end gap-3">
                    @csrf
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Pack</label>
                        <select name="pack_id" required class="rounded-lg border-gray-300 text-sm">
                            @foreach ($packs as $pack)
                                <option value="{{ $pack->id }}">{{ $pack->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Quantite</label>
                        <input type="number" name="quantity" value="1" min="1" max="50" class="w-20 rounded-lg border-gray-300 text-sm">
                    </div>
                    <button class="px-4 py-2 bg-purple-600 text-white rounded-lg text-sm font-medium hover:bg-purple-700">Attribuer</button>
                </form>
            </div>

            {{-- Inventaire --}}
            <div class="bg-white rounded-xl shadow-sm p-5">
                <h3 class="font-semibold text-gray-800 mb-3">Inventaire ({{ $cards->count() }})</h3>
                @if ($cards->isEmpty())
                    <p class="text-sm text-gray-500">Aucune carte.</p>
                @else
                    <div class="border border-gray-200 rounded-lg divide-y divide-gray-100 max-h-96 overflow-y-auto">
                        @foreach ($cards as $uc)
                            <div class="flex items-center gap-3 px-3 py-2">
                                <img src="{{ $uc->card->imageUrl() }}" class="w-9 h-9 rounded object-cover bg-gray-100" alt="">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $uc->card->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $uc->card->rarity->name }} · 🪙 {{ $uc->card->value }}</p>
                                </div>
                                @if ($uc->isLocked())
                                    <span class="text-xs text-amber-600">🔒 echange #{{ $uc->locked_by_trade_id }}</span>
                                @else
                                    <form method="POST" action="{{ route('admin.users.cards.destroy', [$user, $uc]) }}" onsubmit="return confirm('Supprimer cette carte de l\'inventaire ?')">
                                        @csrf @method('DELETE')
                                        <button class="text-xs text-red-500 hover:underline">Supprimer</button>
                                    </form>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
