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

        // Nombre de packs de chaque type possede par le joueur.
        $owned = $user->packs()
            ->selectRaw('pack_id, COUNT(*) as qty')
            ->groupBy('pack_id')
            ->pluck('qty', 'pack_id');

        $packs = Pack::active()->orderBy('name')->get();

        // Cartes tout juste tirees, a reveler (flash de l'action open).
        $revealed = collect();
        $openedPack = null;
        if ($opened = session('opened')) {
            $openedPack = $opened['pack'] ?? null;
            $revealed = \App\Models\Card::with('rarity', 'cardType')
                ->whereIn('id', $opened['cardIds'] ?? [])
                ->get()
                // Preserve l'ordre de tirage, doublons compris.
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

        // Les cartes obtenues sont passees en session pour l'animation de
        // reveal sur la page de resultat (CDC 4.2 etape 4).
        return redirect()
            ->route('packs.index')
            ->with('opened', [
                'pack' => $pack->name,
                'cardIds' => $cards->pluck('card_id')->all(),
            ]);
    }
}
