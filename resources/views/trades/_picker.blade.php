@if ($cards->isEmpty())
    <div class="bg-white rounded-xl shadow-sm p-8 text-center text-sm text-gray-500">Aucune carte echangeable.</div>
@else
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 max-h-[32rem] overflow-y-auto p-1">
        @foreach ($cards as $uc)
            <label class="relative cursor-pointer block">
                <input
                    type="checkbox"
                    name="{{ $name }}[]"
                    value="{{ $uc->id }}"
                    class="peer sr-only"
                    x-model="{{ $group }}"
                >
                <div class="rounded-xl border-2 border-transparent peer-checked:border-purple-500 peer-checked:ring-2 peer-checked:ring-purple-200 transition">
                    <x-game-card :card="$uc->card" />
                </div>
                <div class="absolute top-2 left-2 w-6 h-6 rounded-full bg-white border-2 border-gray-300 peer-checked:bg-purple-600 peer-checked:border-purple-600 flex items-center justify-center text-white text-xs">
                    ✓
                </div>
            </label>
        @endforeach
    </div>
@endif
