@if ($items->isEmpty())
    <p class="text-sm text-gray-400">Rien</p>
@else
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
        @foreach ($items as $item)
            <x-game-card :card="$item->userCard->card" />
        @endforeach
    </div>
@endif
