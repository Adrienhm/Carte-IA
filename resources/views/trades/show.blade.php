<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Echange #{{ $trade->id }}</h2>
    </x-slot>

    @php
        $me = auth()->user();
        $isReceiver = $me->id === $trade->receiver_id;
        $isSender = $me->id === $trade->sender_id;
        $statusColors = [
            'pending' => 'bg-amber-100 text-amber-700',
            'accepted' => 'bg-green-100 text-green-700',
            'rejected' => 'bg-red-100 text-red-700',
            'cancelled' => 'bg-gray-100 text-gray-500',
        ];
    @endphp

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white rounded-xl shadow-sm p-5 flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600"><span class="font-semibold">{{ $trade->sender->name }}</span> → <span class="font-semibold">{{ $trade->receiver->name }}</span></p>
                    @if ($trade->message)
                        <p class="text-sm text-gray-500 mt-1 italic">« {{ $trade->message }} »</p>
                    @endif
                </div>
                <span class="text-sm font-medium px-3 py-1 rounded-full {{ $statusColors[$trade->status] }}">{{ $trade->statusLabel() }}</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white rounded-xl shadow-sm p-5">
                    <h3 class="font-semibold text-gray-800 mb-3">{{ $trade->sender->name }} offre</h3>
                    @include('trades._side', ['items' => $offered])
                </div>
                <div class="bg-white rounded-xl shadow-sm p-5">
                    <h3 class="font-semibold text-gray-800 mb-3">{{ $trade->receiver->name }} donne</h3>
                    @include('trades._side', ['items' => $requested])
                </div>
            </div>

            @if ($trade->isPending())
                <div class="bg-white rounded-xl shadow-sm p-5 flex flex-wrap gap-3 justify-end">
                    @if ($isReceiver)
                        <form method="POST" action="{{ route('trades.reject', $trade) }}">
                            @csrf
                            <button class="px-5 py-2 bg-white border border-red-300 text-red-600 rounded-lg text-sm font-medium hover:bg-red-50">Refuser</button>
                        </form>
                        <form method="POST" action="{{ route('trades.accept', $trade) }}">
                            @csrf
                            <button class="px-5 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700">Accepter l'echange</button>
                        </form>
                    @elseif ($isSender)
                        <p class="text-sm text-gray-500 self-center me-auto">En attente de la reponse de {{ $trade->receiver->name }}.</p>
                        <form method="POST" action="{{ route('trades.cancel', $trade) }}">
                            @csrf
                            <button class="px-5 py-2 bg-white border border-gray-300 text-gray-600 rounded-lg text-sm font-medium hover:bg-gray-50">Annuler ma proposition</button>
                        </form>
                    @endif
                </div>
            @else
                <p class="text-sm text-gray-500 text-center">
                    Echange {{ strtolower($trade->statusLabel()) }}
                    @if ($trade->responded_at) le {{ $trade->responded_at->format('d/m/Y à H:i') }} @endif.
                </p>
            @endif

            <a href="{{ route('trades.index') }}" class="inline-block text-sm text-purple-600 hover:underline">← Retour a mes echanges</a>
        </div>
    </div>
</x-app-layout>
