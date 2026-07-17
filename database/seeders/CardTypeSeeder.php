<?php

namespace Database\Seeders;

use App\Models\CardType;
use Illuminate\Database\Seeder;

/**
 * Types de cartes de depart (CDC 5.1 "Types dynamiques"). L'administrateur
 * peut ensuite en ajouter, modifier ou supprimer depuis le back-office.
 */
class CardTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'name' => 'Guerrier', 'slug' => 'guerrier',
                'description' => 'Combattant de premiere ligne des nations.',
                'prompt_hint' => 'armored warrior holding a weapon, battlefield background',
            ],
            [
                'name' => 'Mage', 'slug' => 'mage',
                'description' => 'Manipulateur des energies arcaniques.',
                'prompt_hint' => 'robed spellcaster channeling magical energy, glowing runes',
            ],
            [
                'name' => 'Batisseur', 'slug' => 'batisseur',
                'description' => 'Ingenieur des fortifications et des territoires.',
                'prompt_hint' => 'engineer with blueprints near a fortress under construction',
            ],
            [
                'name' => 'Espion', 'slug' => 'espion',
                'description' => 'Agent de l\'ombre au service de sa nation.',
                'prompt_hint' => 'hooded rogue in the shadows, daggers, moonlight',
            ],
            [
                'name' => 'Bete', 'slug' => 'bete',
                'description' => 'Creature sauvage apprivoisee pour la guerre.',
                'prompt_hint' => 'fearsome war beast, fangs and claws, wild landscape',
            ],
        ];

        foreach ($types as $type) {
            CardType::updateOrCreate(['slug' => $type['slug']], $type);
        }
    }
}
