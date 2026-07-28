<?php

namespace App\Http\Controllers;

use App\Models\Pack;
use App\Services\PackOpeningService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class PackController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $owned = $user->packs()
            ->selectRaw('pack_id, COUNT(*) as qty')
            ->groupBy('pack_id')
            ->pluck('qty', 'pack_id');

        $packs = Pack::active()->orderBy('name')->get();

        $revealed = collect();
        $openedPack = null;
        if ($opened = session('opened')) {
            $openedPack = $opened['pack'] ?? null;
            $revealed = \App\Models\Card::with('rarity', 'cardType')
                ->whereIn('id', $opened['cardIds'] ?? [])
                ->get()
                ->keyBy('id');
            $revealed = collect($opened['cardIds'] ?? [])->map(fn ($id) => $revealed->get($id))->filter();
        }

        return view('packs.index', [
            'packs' => $packs,
            'owned' => $owned,
            'revealed' => $revealed,
            'openedPack' => $openedPack,
        ]);
    }

    public function open(Request $request, Pack $pack, PackOpeningService $service): RedirectResponse
    {
        try {
            $cards = $service->open($request->user(), $pack);
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('packs.index')
            ->with('opened', [
                'pack' => $pack->name,
                'cardIds' => $cards->pluck('card_id')->all(),
            ]);
    }
}
