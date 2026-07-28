<?php

namespace App\Services;

use InvalidArgumentException;

/**
 * @template T
 */
class WeightedDrawService
{
    /**
     * @param  array<array-key, int>  $weights
     * @return array-key
     */
    public function drawKey(array $weights): int|string
    {
        if ($weights === []) {
            throw new InvalidArgumentException('Aucun candidat a tirer.');
        }

        $total = 0;
        foreach ($weights as $weight) {
            if ($weight < 0) {
                throw new InvalidArgumentException('Un poids ne peut pas etre negatif.');
            }
            $total += $weight;
        }

        if ($total <= 0) {
            throw new InvalidArgumentException('La somme des poids doit etre strictement positive.');
        }

        $roll = random_int(1, $total);

        $cumulative = 0;
        foreach ($weights as $key => $weight) {
            $cumulative += $weight;
            if ($roll <= $cumulative) {
                return $key;
            }
        }

        return array_key_last($weights);
    }

    /**
     * @param  array<array-key, int>  $weights
     * @return list<array-key>
     */
    public function drawMany(array $weights, int $count): array
    {
        if ($count < 1) {
            throw new InvalidArgumentException('Le nombre de tirages doit etre au moins 1.');
        }

        $results = [];
        for ($i = 0; $i < $count; $i++) {
            $results[] = $this->drawKey($weights);
        }

        return $results;
    }
}
