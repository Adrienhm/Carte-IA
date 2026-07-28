<?php

namespace Database\Seeders;

use App\Models\Card;
use App\Models\Pack;
use Illuminate\Database\Seeder;

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

        $classicWeights = [];
        foreach ($cards as $card) {
            $classicWeights[$card->id] = ['weight' => $card->rarity->default_weight];
        }
        $classic->cards()->sync($classicWeights);

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
