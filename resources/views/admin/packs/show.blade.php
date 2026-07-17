<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Pack : {{ $pack->name }}</h2>
            <a href="{{ route('admin.packs.edit', $pack) }}" class="px-4 py-2 bg-purple-600 text-white rounded-lg text-sm font-medium hover:bg-purple-700">Editer</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white rounded-xl shadow-sm p-5">
                <p class="text-sm text-gray-600">{{ $pack->description }}</p>
                <p class="text-xs text-gray-400 mt-2">{{ $pack->cards_per_pack }} cartes par ouverture · somme des poids : {{ $totalWeight }}</p>
            </div>

            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-gray-500">
                        <tr>
                            <th class="px-4 py-3">Carte</th>
                            <th class="px-4 py-3">Rarete</th>
                            <th class="px-4 py-3 text-right">Poids</th>
                            <th class="px-4 py-3 text-right">Probabilite</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($pack->cards->sortByDesc(fn ($c) => $c->pivot->weight) as $card)
                            <tr>
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $card->name }}</td>
                                <td class="px-4 py-3"><x-rarity-badge :rarity="$card->rarity" /></td>
                                <td class="px-4 py-3 text-right text-gray-600">{{ $card->pivot->weight }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-purple-600">
                                    {{ $totalWeight > 0 ? number_format($card->pivot->weight / $totalWeight * 100, 2) : '0' }}%
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
