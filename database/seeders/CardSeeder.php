<?php

namespace Database\Seeders;

use App\Models\CardType;
use App\Models\Rarity;
use App\Services\CardGeneration\CardComposer;
use App\Services\CardGeneration\CardGenerationRequest;
use Illuminate\Database\Seeder;

class CardSeeder extends Seeder
{
    public function run(CardComposer $composer): void
    {
        $types = CardType::all();
        $rarities = Rarity::orderBy('sort_order')->get()->keyBy('slug');

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
