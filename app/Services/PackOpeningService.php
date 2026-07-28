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
            $userPack = UserPack::query()
                ->where('user_id', $user->id)
                ->where('pack_id', $pack->id)
                ->lockForUpdate()
                ->first();

            if (! $userPack) {
                throw new RuntimeException("Vous ne possedez pas ce pack.");
            }

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

            $userPack->delete();

            return $obtained;
        });
    }
}
