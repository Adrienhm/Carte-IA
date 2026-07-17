<?php

namespace App\Services\CardGeneration;

use App\Models\CardType;
use App\Models\Rarity;

/**
 * Parametres d'entree d'une generation : la rarete et le type fixent le cadre,
 * le nom est optionnel (l'IA en propose un si l'admin n'en impose pas).
 */
class CardGenerationRequest
{
    public function __construct(
        public readonly CardType $cardType,
        public readonly Rarity $rarity,
        public readonly ?string $name = null,
    ) {
    }
}
