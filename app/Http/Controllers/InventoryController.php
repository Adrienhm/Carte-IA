<?php

namespace App\Http\Controllers;

use App\Models\CardType;
use App\Models\Rarity;
use App\Models\UserCard;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $validated = $request->validate([
            'type' => ['nullable', 'exists:card_types,id'],
            'rarity' => ['nullable', 'exists:rarities,id'],
        ]);

        $query = UserCard::query()
            ->where('user_cards.user_id', $user->id)
            ->join('cards', 'cards.id', '=', 'user_cards.card_id')
            ->when($validated['type'] ?? null, fn ($q, $type) => $q->where('cards.card_type_id', $type))
            ->when($validated['rarity'] ?? null, fn ($q, $rarity) => $q->where('cards.rarity_id', $rarity))
            ->selectRaw('user_cards.card_id, COUNT(*) as qty, SUM(user_cards.locked_by_trade_id IS NOT NULL) as locked_qty')
            ->groupBy('user_cards.card_id')
            ->orderByDesc('cards.value');

        $grouped = $query->get()->load('card.rarity', 'card.cardType');

        $totalValue = $grouped->sum(fn ($row) => $row->qty * $row->card->value);
        $totalCards = $grouped->sum('qty');

        return view('inventory.index', [
            'grouped' => $grouped,
            'totalValue' => $totalValue,
            'totalCards' => $totalCards,
            'types' => CardType::orderBy('name')->get(),
            'rarities' => Rarity::orderBy('sort_order')->get(),
            'selectedType' => $validated['type'] ?? null,
            'selectedRarity' => $validated['rarity'] ?? null,
        ]);
    }
}
