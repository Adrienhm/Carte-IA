<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserCard;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlayerController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();

        $players = User::query()
            ->whereKeyNot($request->user()->id)
            ->when($search !== '', fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->withCount('cards')
            ->orderBy('name')
            ->paginate(24)
            ->withQueryString();

        return view('players.index', [
            'players' => $players,
            'search' => $search,
        ]);
    }

    public function show(Request $request, User $user): View
    {
        $grouped = UserCard::query()
            ->where('user_cards.user_id', $user->id)
            ->join('cards', 'cards.id', '=', 'user_cards.card_id')
            ->selectRaw('user_cards.card_id, COUNT(*) as qty')
            ->groupBy('user_cards.card_id')
            ->orderByDesc('cards.value')
            ->get()
            ->load('card.rarity', 'card.cardType');

        return view('players.show', [
            'player' => $user,
            'grouped' => $grouped,
            'totalCards' => $grouped->sum('qty'),
            'collectionValue' => $user->collectionValue(),
            'isSelf' => $request->user()->id === $user->id,
        ]);
    }
}
