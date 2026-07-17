<?php

namespace Tests;

use App\Models\Card;
use App\Models\CardType;
use App\Models\Pack;
use App\Models\Rarity;
use App\Models\User;
use App\Models\UserCard;
use Illuminate\Support\Facades\Hash;

/**
 * Fabriques minimales partagees par les tests. On construit les entites a la
 * main plutot que via des factories Eloquent : le schema est specifique au
 * projet et ces helpers restent lisibles.
 */
trait GameTestHelpers
{
    protected function makeRarity(array $overrides = []): Rarity
    {
        static $i = 0;
        $i++;

        return Rarity::create(array_merge([
            'name' => 'Rarete '.$i,
            'slug' => 'rarete-'.$i,
            'color' => '#123456',
            'default_weight' => 10,
            'base_value' => 10,
            'min_stat' => 10,
            'max_stat' => 20,
            'sort_order' => $i,
        ], $overrides));
    }

    protected function makeType(array $overrides = []): CardType
    {
        static $i = 0;
        $i++;

        return CardType::create(array_merge([
            'name' => 'Type '.$i,
            'slug' => 'type-'.$i,
            'is_active' => true,
        ], $overrides));
    }

    protected function makeCard(Rarity $rarity = null, CardType $type = null, array $overrides = []): Card
    {
        $rarity ??= $this->makeRarity();
        $type ??= $this->makeType();

        return Card::create(array_merge([
            'name' => 'Carte '.uniqid(),
            'description' => 'desc',
            'card_type_id' => $type->id,
            'rarity_id' => $rarity->id,
            'value' => $rarity->base_value,
            'power' => 10,
            'defense' => 10,
            'is_active' => true,
        ], $overrides));
    }

    protected function makeUser(array $overrides = []): User
    {
        // is_admin n'est pas mass-assignable (protection contre l'elevation de
        // privileges) : on le force explicitement quand un test le demande.
        $isAdmin = (bool) ($overrides['is_admin'] ?? false);
        unset($overrides['is_admin']);

        $user = User::create(array_merge([
            'name' => 'Joueur '.uniqid(),
            'email' => uniqid().'@test.local',
            'password' => Hash::make('password'),
        ], $overrides));

        if ($isAdmin) {
            $user->forceFill(['is_admin' => true])->save();
        }

        return $user;
    }

    protected function makeAdmin(): User
    {
        return $this->makeUser(['is_admin' => true]);
    }

    /**
     * Donne un exemplaire d'une carte a un joueur et le renvoie.
     */
    protected function giveCard(User $user, Card $card): UserCard
    {
        return UserCard::create([
            'user_id' => $user->id,
            'card_id' => $card->id,
            'source' => 'test',
        ]);
    }

    /**
     * Cree un pack contenant les cartes fournies avec leurs poids.
     *
     * @param  array<int, int>  $cardWeights  [card_id => weight]
     */
    protected function makePack(array $cardWeights, int $cardsPerPack = 5): Pack
    {
        $pack = Pack::create([
            'name' => 'Pack '.uniqid(),
            'cards_per_pack' => $cardsPerPack,
            'is_active' => true,
        ]);

        $sync = [];
        foreach ($cardWeights as $cardId => $weight) {
            $sync[$cardId] = ['weight' => $weight];
        }
        $pack->cards()->sync($sync);

        return $pack;
    }
}
