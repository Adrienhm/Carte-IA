<?php

namespace Database\Seeders;

use App\Models\CardType;
use App\Models\Rarity;
use App\Services\CardGeneration\CardComposer;
use App\Services\CardGeneration\CardGenerationRequest;
use Illuminate\Database\Seeder;

/**
 * Peuple le catalogue via le service de generation IA lui-meme (driver "fake"
 * par defaut), ce qui exerce toute la chaine de generation et produit des
 * illustrations de demonstration. Chaque type recoit des cartes reparties sur
 * les quatre raretes.
 */
class CardSeeder extends Seeder
{
    public function run(CardComposer $composer): void
    {
        $types = CardType::all();
        $rarities = Rarity::orderBy('sort_order')->get()->keyBy('slug');

        // Nombre de cartes generees par type et par rarete. On produit plus de
        // communes que de legendaires, cote catalogue comme cote tirage.
        $countByRarity = [
            'commune' => 3,
            'rare' => 2,
            'epique' => 2,
            'legendaire' => 1,
        ];

        foreach ($types as $type) {
            foreach ($countByRarity as $raritySlug => $count) {
                $rarity = $rarities->get($raritySlug);
                if (! $rarity instanceof Rarity) {
                    continue;
                }

                for ($i = 0; $i < $count; $i++) {
                    $card = $composer->compose(new CardGenerationRequest($type, $rarity));
                    $card->save();
                }
            }
        }
    }
}
