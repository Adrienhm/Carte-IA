<?php

namespace Database\Seeders;

use App\Models\Rarity;
use Illuminate\Database\Seeder;

/**
 * Raretes et equilibrage economique (CDC 7).
 *
 * Distribution cible a l'ouverture (CDC 7.1) : Commune 70 %, Rare 20 %,
 * Epique 9 %, Legendaire 1 %. Les "default_weight" reproduisent ce ratio
 * (70/20/9/1) et servent de poids par defaut quand une carte est ajoutee a un
 * pack.
 *
 * Grille de valeurs (CDC 7.2) : la valeur croit fortement avec la rarete afin
 * qu'une legendaire (obtenue ~1 fois sur 100) vaille bien plus qu'une pile de
 * communes. Ratio commune -> legendaire = 1:50, justifie dans le rapport.
 */
class RaritySeeder extends Seeder
{
    public function run(): void
    {
        $rarities = [
            [
                'name' => 'Commune', 'slug' => 'commune', 'color' => '#9ca3af',
                'default_weight' => 70, 'base_value' => 10,
                'min_stat' => 10, 'max_stat' => 40, 'sort_order' => 1,
            ],
            [
                'name' => 'Rare', 'slug' => 'rare', 'color' => '#3b82f6',
                'default_weight' => 20, 'base_value' => 40,
                'min_stat' => 35, 'max_stat' => 60, 'sort_order' => 2,
            ],
            [
                'name' => 'Epique', 'slug' => 'epique', 'color' => '#8b5cf6',
                'default_weight' => 9, 'base_value' => 120,
                'min_stat' => 55, 'max_stat' => 80, 'sort_order' => 3,
            ],
            [
                'name' => 'Legendaire', 'slug' => 'legendaire', 'color' => '#f59e0b',
                'default_weight' => 1, 'base_value' => 500,
                'min_stat' => 75, 'max_stat' => 100, 'sort_order' => 4,
            ],
        ];

        foreach ($rarities as $rarity) {
            Rarity::updateOrCreate(['slug' => $rarity['slug']], $rarity);
        }
    }
}
