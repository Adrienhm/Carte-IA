<?php

namespace App\Services;

use App\Models\Trade;
use App\Models\TradeItem;
use App\Models\User;
use App\Models\UserCard;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Echange securise entre joueurs (CDC 4.2, 5.1, 6).
 *
 * Regles garanties par ce service :
 *  - possession reelle verifiee cote serveur a la creation ET a l'acceptation ;
 *  - blocage des exemplaires engages tant que l'echange est en attente ;
 *  - transfert atomique tout-ou-rien (aucune carte dupliquee ni perdue) ;
 *  - validation bilaterale (seul le destinataire accepte/refuse).
 */
class TradeService
{
    /**
     * Cree une proposition d'echange et bloque les cartes concernees.
     *
     * @param  list<int>  $offeredUserCardIds  exemplaires de l'initiateur
     * @param  list<int>  $requestedUserCardIds exemplaires du destinataire
     */
    public function propose(
        User $sender,
        User $receiver,
        array $offeredUserCardIds,
        array $requestedUserCardIds,
        ?string $message = null,
    ): Trade {
        if ($sender->id === $receiver->id) {
            throw new RuntimeException('Vous ne pouvez pas echanger avec vous-meme.');
        }

        if ($offeredUserCardIds === [] && $requestedUserCardIds === []) {
            throw new RuntimeException('Un echange doit impliquer au moins une carte.');
        }

        return DB::transaction(function () use ($sender, $receiver, $offeredUserCardIds, $requestedUserCardIds, $message) {
            // Verrouille et valide les exemplaires de chaque camp. Le verrou
            // pessimiste empeche qu'un meme exemplaire soit engage dans deux
            // echanges crees simultanement.
            $offered = $this->lockAndValidateOwnership($offeredUserCardIds, $sender);
            $requested = $this->lockAndValidateOwnership($requestedUserCardIds, $receiver);

            $trade = Trade::create([
                'sender_id' => $sender->id,
                'receiver_id' => $receiver->id,
                'status' => Trade::STATUS_PENDING,
                'message' => $message,
            ]);

            $this->attachItems($trade, $offered, TradeItem::SIDE_OFFERED);
            $this->attachItems($trade, $requested, TradeItem::SIDE_REQUESTED);

            // Blocage : les exemplaires ne peuvent plus etre engages ailleurs
            // tant que cet echange est en attente (CDC 5.1).
            $this->lockCards($offered->merge($requested), $trade);

            return $trade;
        });
    }

    /**
     * Le destinataire accepte : les cartes changent de proprietaire.
     */
    public function accept(Trade $trade, User $actingUser): void
    {
        DB::transaction(function () use ($trade, $actingUser) {
            $trade = Trade::query()->lockForUpdate()->findOrFail($trade->id);

            if ($actingUser->id !== $trade->receiver_id) {
                throw new RuntimeException("Seul le destinataire peut accepter cet echange.");
            }

            $this->assertPending($trade);

            $items = $trade->items()->with('userCard')->get();

            // Re-verification de possession au moment de l'acceptation : on
            // s'assure que chaque exemplaire appartient toujours au bon joueur
            // et reste bien bloque par cet echange (CDC 6).
            foreach ($items as $item) {
                $card = $item->userCard;
                $expectedOwner = $item->side === TradeItem::SIDE_OFFERED
                    ? $trade->sender_id
                    : $trade->receiver_id;

                if (! $card || $card->user_id !== $expectedOwner || $card->locked_by_trade_id !== $trade->id) {
                    throw new RuntimeException(
                        "L'echange n'est plus valide : une carte a change de proprietaire ou de statut."
                    );
                }
            }

            // Transfert : offert -> destinataire, demande -> initiateur.
            foreach ($items as $item) {
                $newOwner = $item->side === TradeItem::SIDE_OFFERED
                    ? $trade->receiver_id
                    : $trade->sender_id;

                $item->userCard->update([
                    'user_id' => $newOwner,
                    'source' => 'trade',
                    'locked_by_trade_id' => null,
                ]);
            }

            $trade->update([
                'status' => Trade::STATUS_ACCEPTED,
                'responded_at' => now(),
            ]);
        });
    }

    /**
     * Le destinataire refuse : les cartes sont debloquees, rien ne bouge.
     */
    public function reject(Trade $trade, User $actingUser): void
    {
        DB::transaction(function () use ($trade, $actingUser) {
            $trade = Trade::query()->lockForUpdate()->findOrFail($trade->id);

            if ($actingUser->id !== $trade->receiver_id) {
                throw new RuntimeException("Seul le destinataire peut refuser cet echange.");
            }

            $this->assertPending($trade);
            $this->releaseCards($trade);

            $trade->update([
                'status' => Trade::STATUS_REJECTED,
                'responded_at' => now(),
            ]);
        });
    }

    /**
     * L'initiateur annule sa propre proposition encore en attente.
     */
    public function cancel(Trade $trade, User $actingUser): void
    {
        DB::transaction(function () use ($trade, $actingUser) {
            $trade = Trade::query()->lockForUpdate()->findOrFail($trade->id);

            if ($actingUser->id !== $trade->sender_id) {
                throw new RuntimeException("Seul l'initiateur peut annuler cet echange.");
            }

            $this->assertPending($trade);
            $this->releaseCards($trade);

            $trade->update([
                'status' => Trade::STATUS_CANCELLED,
                'responded_at' => now(),
            ]);
        });
    }

    /**
     * Verrouille les exemplaires vises et verifie qu'ils appartiennent bien a
     * $owner et qu'ils sont disponibles (non deja engages ailleurs).
     *
     * @param  list<int>  $userCardIds
     * @return \Illuminate\Support\Collection<int, UserCard>
     */
    private function lockAndValidateOwnership(array $userCardIds, User $owner)
    {
        $ids = array_values(array_unique(array_map('intval', $userCardIds)));

        if ($ids === []) {
            return collect();
        }

        $cards = UserCard::query()
            ->whereIn('id', $ids)
            ->lockForUpdate()
            ->get();

        if ($cards->count() !== count($ids)) {
            throw new RuntimeException("Une des cartes selectionnees est introuvable.");
        }

        foreach ($cards as $card) {
            if ($card->user_id !== $owner->id) {
                throw new RuntimeException("{$owner->name} ne possede pas l'une des cartes selectionnees.");
            }
            if ($card->locked_by_trade_id !== null) {
                throw new RuntimeException("Une des cartes est deja engagee dans un autre echange.");
            }
        }

        return $cards;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, UserCard>  $cards
     */
    private function attachItems(Trade $trade, $cards, string $side): void
    {
        foreach ($cards as $card) {
            TradeItem::create([
                'trade_id' => $trade->id,
                'user_card_id' => $card->id,
                'side' => $side,
            ]);
        }
    }

    /**
     * @param  \Illuminate\Support\Collection<int, UserCard>  $cards
     */
    private function lockCards($cards, Trade $trade): void
    {
        UserCard::query()
            ->whereIn('id', $cards->pluck('id'))
            ->update(['locked_by_trade_id' => $trade->id]);
    }

    private function releaseCards(Trade $trade): void
    {
        UserCard::query()
            ->where('locked_by_trade_id', $trade->id)
            ->update(['locked_by_trade_id' => null]);
    }

    private function assertPending(Trade $trade): void
    {
        if (! $trade->isPending()) {
            throw new RuntimeException("Cet echange a deja ete traite.");
        }
    }
}
