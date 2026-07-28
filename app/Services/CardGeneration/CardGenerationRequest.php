<?php

namespace App\Services\CardGeneration;

use App\Models\CardType;
use App\Models\Rarity;

class CardGenerationRequest
{
    public function __construct(
        public readonly CardType $cardType,
        public readonly Rarity $rarity,
        public readonly ?string $name = null,
    ) {
    }
}
