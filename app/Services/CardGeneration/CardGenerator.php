<?php

namespace App\Services\CardGeneration;

interface CardGenerator
{
    /**
     * @throws CardGenerationException en cas d'echec non recuperable.
     */
    public function generate(CardGenerationRequest $request): GeneratedCard;
}
