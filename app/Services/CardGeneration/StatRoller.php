<?php

namespace App\Services\CardGeneration;

use App\Models\Rarity;

class StatRoller
{
    /**
     * @return array{power: int, defense: int}
     */
    public function roll(Rarity $rarity): array
    {
        return [
            'power' => $this->rollOne($rarity),
            'defense' => $this->rollOne($rarity),
        ];
    }

    private function rollOne(Rarity $rarity): int
    {
        $min = $rarity->min_stat;
        $max = max($rarity->max_stat, $min);

        return random_int($min, $max);
    }
}
