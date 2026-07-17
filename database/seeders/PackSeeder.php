<?php

namespace Database\Seeders;

use App\Models\Card;
use App\Models\Pack;
use Illuminate\Database\Seeder;

/**
 * Packs de demonstration. Chaque carte recoit comme poids le "default_weight"
 * de sa rarete (70/20/9/1), ce qui reproduit dans le pack la distribution
 * cible du CDC 7.1 : ~70 % commune, ~20 % rare, ~9 % epique, ~1 % legendaire.
 */
class PackSeeder extends Seeder
{
    public function run(): void
    {
        $classic = Pack::updateOrCreate(
            ['name' => 'Pack Classique'],
            [
                'description' => 'Le pack standard : 5 cartes tirees selon les probabilites usuelles.',
                'cards_per_pack' => 5,
                'is_active' => true,
            ],
        );

        $premium = Pack::updateOrCreate(
            ['name' => 'Pack Premium'],
            [
                'description' => 'Un pack plus genereux : 5 cartes avec de meilleures chances de rarete.',
                'cards_per_pack' => 5,
                'is_active' => true,
            ],
        );

        $cards = Card::with('rarity')->get();

        // Pack Classique : poids = distribution de reference de la rarete.
        $classicWeights = [];
        foreach ($cards as $card) {
            $classicWeights[$card->id] = ['weight' => $card->rarity->default_weight];
        }
        $classic->cards()->sync($classicWeights);

        // Pack Premium : on releve les poids des raretes elevees pour les
        // rendre sensiblement plus frequentes (choix d'equilibrage documente).
        $premiumBoost = [
            'commune' => 40,
            'rare' => 30,
            'epique' => 20,
            'legendaire' => 5,
        ];
        $premiumWeights = [];
        foreach ($cards as $card) {
            $premiumWeights[$card->id] = ['weight' => $premiumBoost[$card->rarity->slug] ?? $card->rarity->default_weight];
        }
        $premium->cards()->sync($premiumWeights);
    }
}
