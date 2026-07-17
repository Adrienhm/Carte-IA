<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Mes echanges</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            @php
                $statusColors = [
                    'pending' => 'bg-amber-100 text-amber-700',
                    'accepted' => 'bg-green-100 text-green-700',
                    'rejected' => 'bg-red-100 text-red-700',
                    'cancelled' => 'bg-gray-100 text-gray-500',
                ];
            @endphp

            <section>
                <h3 class="font-semibold text-gray-800 mb-3">Recus ({{ $received->count() }})</h3>
                @if ($received->isEmpty())
                    <p class="text-sm text-gray-500 bg-white rounded-xl shadow-sm p-6">Aucune proposition recue.</p>
                @else
                    <div class="space-y-3">
                        @foreach ($received as $trade)
                            <a href="{{ route('trades.show', $trade) }}" class="block bg-white rounded-xl shadow-sm p-4 hover:shadow-md transition">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-medium text-gray-900">De {{ $trade->sender->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $trade->items->where('side','offered')->count() }} offerte(s) contre {{ $trade->items->where('side','requested')->count() }} demandee(s) · {{ $trade->created_at->diffForHumans() }}</p>
                                    </div>
                                    <span class="text-xs font-medium px-2 py-1 rounded-full {{ $statusColors[$trade->status] }}">{{ $trade->statusLabel() }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </section>

            <section>
                <h3 class="font-semibold text-gray-800 mb-3">Envoyes ({{ $sent->count() }})</h3>
                @if ($sent->isEmpty())
                    <p class="text-sm text-gray-500 bg-white rounded-xl shadow-sm p-6">Aucune proposition envoyee. <a href="{{ route('players.index') }}" class="text-purple-600 underline">Trouvez un joueur</a> pour proposer un echange.</p>
                @else
                    <div class="space-y-3">
                        @foreach ($sent as $trade)
                            <a href="{{ route('trades.show', $trade) }}" class="block bg-white rounded-xl shadow-sm p-4 hover:shadow-md transition">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-medium text-gray-900">A {{ $trade->receiver->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $trade->items->where('side','offered')->count() }} offerte(s) contre {{ $trade->items->where('side','requested')->count() }} demandee(s) · {{ $trade->created_at->diffForHumans() }}</p>
                                    </div>
                                    <span class="text-xs font-medium px-2 py-1 rounded-full {{ $statusColors[$trade->status] }}">{{ $trade->statusLabel() }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
