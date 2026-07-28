<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Proposer un echange a {{ $partner->name }}</h2>
    </x-slot>

    <div class="py-8" x-data="{ offered: [], requested: [] }">
        <form method="POST" action="{{ route('trades.store') }}" class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @csrf
            <input type="hidden" name="receiver_id" value="{{ $partner->id }}">

            <div class="bg-white rounded-xl shadow-sm p-4 flex flex-wrap items-center gap-4">
                <p class="text-sm text-gray-600">
                    Vous offrez <span class="font-bold text-purple-600" x-text="offered.length"></span> carte(s)
                    contre <span class="font-bold text-purple-600" x-text="requested.length"></span> carte(s) de {{ $partner->name }}.
                </p>
                <div class="ms-auto flex items-center gap-3">
                    <input type="text" name="message" maxlength="255" placeholder="Message (optionnel)" class="rounded-lg border-gray-300 text-sm">
                    <button type="submit"
                            x-bind:disabled="offered.length === 0 && requested.length === 0"
                            class="px-5 py-2 bg-purple-600 text-white rounded-lg text-sm font-medium hover:bg-purple-700 disabled:bg-gray-300 disabled:cursor-not-allowed">
                        Envoyer la proposition
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <h3 class="font-semibold text-gray-800 mb-3">Mes cartes ({{ $myCards->count() }} disponibles)</h3>
                    @include('trades._picker', ['cards' => $myCards, 'group' => 'offered', 'name' => 'offered'])
                </div>

                <div>
                    <h3 class="font-semibold text-gray-800 mb-3">Cartes de {{ $partner->name }} ({{ $theirCards->count() }} disponibles)</h3>
                    @include('trades._picker', ['cards' => $theirCards, 'group' => 'requested', 'name' => 'requested'])
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
