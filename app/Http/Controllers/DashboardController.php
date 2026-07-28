<?php

namespace App\Http\Controllers;

use App\Models\Rarity;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $byRarity = Rarity::orderBy('sort_order')
            ->withCount(['cards as owned_count' => fn ($q) => $q->join('user_cards', 'user_cards.card_id', '=', 'cards.id')->where('user_cards.user_id', $user->id)])
            ->get();

        return view('dashboard', [
            'packsOwned' => $user->packs()->count(),
            'cardsOwned' => $user->cards()->count(),
            'collectionValue' => $user->collectionValue(),
            'pendingReceived' => $user->receivedTrades()->pending()->count(),
            'pendingSent' => $user->sentTrades()->pending()->count(),
            'byRarity' => $byRarity,
        ]);
    }
}
