<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pack;
use App\Models\User;
use App\Models\UserCard;
use App\Models\UserPack;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();

        $users = User::query()
            ->when($search !== '', fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
            ->withCount('cards')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'search' => $search,
        ]);
    }

    public function show(User $user): View
    {
        $grouped = UserCard::query()
            ->where('user_cards.user_id', $user->id)
            ->join('cards', 'cards.id', '=', 'user_cards.card_id')
            ->select('user_cards.*')
            ->orderByDesc('cards.value')
            ->with('card.rarity', 'card.cardType', 'lockingTrade')
            ->get();

        return view('admin.users.show', [
            'user' => $user,
            'cards' => $grouped,
            'collectionValue' => $user->collectionValue(),
            'packsOwned' => $user->packs()->count(),
            'packs' => Pack::orderBy('name')->get(),
        ]);
    }

    public function ban(Request $request, User $user): RedirectResponse
    {
        if ($user->isAdmin()) {
            return back()->with('error', 'Impossible de bannir un administrateur.');
        }

        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $user->update([
            'banned_at' => now(),
            'ban_reason' => $validated['reason'] ?? null,
        ]);

        return back()->with('success', $user->name.' a ete banni.');
    }

    public function unban(User $user): RedirectResponse
    {
        $user->update(['banned_at' => null, 'ban_reason' => null]);

        return back()->with('success', $user->name.' a ete debanni.');
    }

    /**
     * Supprime un exemplaire precis de l'inventaire d'un joueur (CDC 4.3).
     */
    public function destroyCard(User $user, UserCard $userCard): RedirectResponse
    {
        abort_unless($userCard->user_id === $user->id, 404);

        if ($userCard->isLocked()) {
            return back()->with('error', 'Cette carte est engagee dans un echange en cours.');
        }

        $userCard->delete();

        return back()->with('success', 'Carte retiree de l\'inventaire du joueur.');
    }

    /**
     * Attribue un pack a un joueur (utile pour la demo et les recompenses).
     */
    public function grantPack(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'pack_id' => ['required', 'exists:packs,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:50'],
        ]);

        for ($i = 0; $i < $validated['quantity']; $i++) {
            UserPack::create([
                'user_id' => $user->id,
                'pack_id' => $validated['pack_id'],
                'source' => 'admin',
            ]);
        }

        return back()->with('success', $validated['quantity'].' pack(s) attribue(s) a '.$user->name.'.');
    }
}
