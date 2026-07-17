<?php

namespace App\Services;

use App\Models\Pack;
use App\Models\PackOpening;
use App\Models\User;
use App\Models\UserCard;
use App\Models\UserPack;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Ouverture d'un pack par un joueur (CDC 4.2 parcours principal).
 *
 * Tout se joue cote serveur, dans une transaction : on verrouille un
 * exemplaire de pack possede par le joueur, on tire N cartes selon les poids
 * de la composition, on les ajoute a l'inventaire et on consomme le pack.
 * L'ensemble est atomique : en cas d'erreur, rien n'est distribue et le pack
 * n'est pas consomme (CDC 6 "Integrite des donnees").
 */
class PackOpeningService
{
    public function __construct(private readonly WeightedDrawService $draw)
    {
    }

    /**
     * @return Collection<int, UserCard> les exemplaires obtenus
     */
    public function open(User $user, Pack $pack): Collection
    {
        return DB::transaction(function () use ($user, $pack) {
            // Verrou pessimiste : empeche une double ouverture concurrente du
            // meme pack (deux onglets, double clic) de dupliquer les cartes.
            $userPack = UserPack::query()
                ->where('user_id', $user->id)
                ->where('pack_id', $pack->id)
                ->lockForUpdate()
                ->first();

            if (! $userPack) {
                throw new RuntimeException("Vous ne possedez pas ce pack.");
            }

            // On recharge la composition dans la transaction pour tirer sur des
            // poids a jour, jamais sur des valeurs venues du client.
            $pack->load('cards');

            if (! $pack->isOpenable()) {
                throw new RuntimeException("Ce pack n'est pas ouvrable (aucune carte configuree).");
            }

            $weights = [];
            foreach ($pack->cards as $card) {
                $weights[$card->id] = (int) $card->pivot->weight;
            }

            $drawnCardIds = $this->draw->drawMany($weights, $pack->cards_per_pack);

            $opening = PackOpening::create([
                'user_id' => $user->id,
                'pack_id' => $pack->id,
            ]);

            $obtained = collect();
            foreach ($drawnCardIds as $cardId) {
                $obtained->push(UserCard::create([
                    'user_id' => $user->id,
                    'card_id' => $cardId,
                    'source' => 'pack',
                    'pack_opening_id' => $opening->id,
                ]));
            }

            // Le pack est consomme apres un tirage reussi.
            $userPack->delete();

            return $obtained;
        });
    }
}
