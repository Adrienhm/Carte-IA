<?php

namespace App\Http\Controllers;

use App\Models\Trade;
use App\Models\User;
use App\Models\UserCard;
use App\Services\TradeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Throwable;

class TradeController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $received = $user->receivedTrades()
            ->with(['sender', 'items.userCard.card.rarity'])
            ->latest()
            ->get();

        $sent = $user->sentTrades()
            ->with(['receiver', 'items.userCard.card.rarity'])
            ->latest()
            ->get();

        return view('trades.index', [
            'received' => $received,
            'sent' => $sent,
        ]);
    }

    public function create(Request $request, User $user): View
    {
        $me = $request->user();

        abort_if($user->id === $me->id, 403, 'Vous ne pouvez pas echanger avec vous-meme.');

        return view('trades.create', [
            'partner' => $user,
            'myCards' => $this->availableCards($me),
            'theirCards' => $this->availableCards($user),
        ]);
    }

    public function store(Request $request, TradeService $service): RedirectResponse
    {
        $validated = $request->validate([
            'receiver_id' => ['required', 'exists:users,id', 'different:'.$request->user()->id],
            'offered' => ['array'],
            'offered.*' => ['integer'],
            'requested' => ['array'],
            'requested.*' => ['integer'],
            'message' => ['nullable', 'string', 'max:255'],
        ]);

        $receiver = User::findOrFail($validated['receiver_id']);

        try {
            $trade = $service->propose(
                $request->user(),
                $receiver,
                $validated['offered'] ?? [],
                $validated['requested'] ?? [],
                $validated['message'] ?? null,
            );
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()
            ->route('trades.show', $trade)
            ->with('success', 'Proposition d\'echange envoyee a '.$receiver->name.'.');
    }

    public function show(Request $request, Trade $trade): View
    {
        abort_unless($trade->involves($request->user()), 403);

        $trade->load(['sender', 'receiver', 'items.userCard.card.rarity', 'items.userCard.card.cardType']);

        return view('trades.show', [
            'trade' => $trade,
            'offered' => $trade->items->where('side', 'offered'),
            'requested' => $trade->items->where('side', 'requested'),
        ]);
    }

    public function accept(Request $request, Trade $trade, TradeService $service): RedirectResponse
    {
        return $this->resolve($request, $trade, fn () => $service->accept($trade, $request->user()), 'Echange accepte : les cartes ont ete transferees.');
    }

    public function reject(Request $request, Trade $trade, TradeService $service): RedirectResponse
    {
        return $this->resolve($request, $trade, fn () => $service->reject($trade, $request->user()), 'Echange refuse.');
    }

    public function cancel(Request $request, Trade $trade, TradeService $service): RedirectResponse
    {
        return $this->resolve($request, $trade, fn () => $service->cancel($trade, $request->user()), 'Echange annule.');
    }

    private function resolve(Request $request, Trade $trade, callable $action, string $success): RedirectResponse
    {
        abort_unless($trade->involves($request->user()), 403);

        try {
            $action();
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('trades.show', $trade)->with('success', $success);
    }

    /**
     * @return Collection<int, UserCard>
     */
    private function availableCards(User $user): Collection
    {
        return UserCard::query()
            ->where('user_id', $user->id)
            ->whereNull('locked_by_trade_id')
            ->with('card.rarity', 'card.cardType')
            ->get()
            ->sortByDesc(fn (UserCard $uc) => $uc->card->value)
            ->values();
    }
}
