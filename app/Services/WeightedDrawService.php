<?php

namespace App\Services;

use InvalidArgumentException;

/**
 * Tirage aleatoire pondere (CDC glossaire "Poids").
 *
 * Chaque candidat porte un poids entier positif ; sa probabilite de sortie est
 * son poids divise par la somme des poids. Le calcul est volontairement isole
 * ici, sans dependance a la requete HTTP, pour qu'il ne puisse s'executer que
 * cote serveur (CDC 5.1 "Anti-triche probabilites") et rester testable.
 *
 * @template T
 */
class WeightedDrawService
{
    /**
     * Tire une cle parmi un tableau [cle => poids].
     *
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

        // random_int est un generateur cryptographique : impossible a predire
        // ou a rejouer cote client (CDC 5.1 anti-triche).
        $roll = random_int(1, $total);

        $cumulative = 0;
        foreach ($weights as $key => $weight) {
            $cumulative += $weight;
            if ($roll <= $cumulative) {
                return $key;
            }
        }

        // Inatteignable : $roll <= $total garantit une sortie dans la boucle.
        return array_key_last($weights);
    }

    /**
     * Effectue $count tirages avec remise et renvoie les cles tirees.
     *
     * Le tirage se fait avec remise : une meme carte peut sortir plusieurs
     * fois dans un pack, ce qui produit naturellement des doublons echangeables
     * (choix d'equilibrage documente dans le rapport).
     *
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
