<?php

namespace App\Services\CardGeneration;

/**
 * Contrat commun a tous les generateurs de cartes (CDC 8.1 "service de
 * generation IA"). Permet d'echanger le driver reel (OpenAI) contre un driver
 * de demonstration sans toucher au reste de l'application.
 */
interface CardGenerator
{
    /**
     * Produit le contenu d'une carte a partir de son type et de sa rarete.
     *
     * @throws CardGenerationException en cas d'echec non recuperable.
     */
    public function generate(CardGenerationRequest $request): GeneratedCard;
}
