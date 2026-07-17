<?php

namespace App\Services\CardGeneration;

/**
 * Resultat d'une generation IA : le contenu textuel et statistique d'une
 * carte, plus le contenu binaire de son illustration.
 *
 * L'image est transportee comme des octets bruts (et non un chemin) pour que
 * le service de generation reste ignorant du stockage : c'est l'appelant qui
 * decide ou et comment persister le fichier (CDC 9.3).
 */
class GeneratedCard
{
    public function __construct(
        public readonly string $name,
        public readonly string $description,
        public readonly int $power,
        public readonly int $defense,
        public readonly string $imagePrompt,
        public readonly ?string $imageContents,
        public readonly string $imageExtension = 'png',
    ) {
    }
}
