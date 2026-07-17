<?php

namespace App\Services\CardGeneration;

/**
 * Construit dynamiquement les prompts envoyes a l'IA a partir des parametres
 * de la carte (CDC 9.2). Centralise ici pour que les differents drivers
 * partagent exactement la meme logique de prompt.
 */
class PromptBuilder
{
    /**
     * Prompt pour l'API d'image, suivant le patron suggere par le CDC 9.2.
     */
    public function imagePrompt(CardGenerationRequest $request, string $cardName): string
    {
        $theme = config('cards.theme');
        $style = config('cards.image_style');
        $hint = $request->cardType->prompt_hint;

        $prompt = sprintf(
            'A %s %s card for a %s, named "%s", %s',
            strtolower($request->rarity->name),
            strtolower($request->cardType->name),
            $theme,
            $cardName,
            $style,
        );

        if ($hint) {
            $prompt .= '. '.$hint;
        }

        return $prompt;
    }

    /**
     * Consigne systeme pour l'API de texte : cadre l'univers et impose une
     * sortie JSON exploitable directement.
     */
    public function textSystemPrompt(): string
    {
        return 'You design collectible cards for '.config('cards.theme').'. '
            .'Answer only with strict JSON, no markdown, no commentary.';
    }

    /**
     * Consigne utilisateur pour l'API de texte : demande nom et description
     * coherents avec le type et la rarete fournis.
     */
    public function textUserPrompt(CardGenerationRequest $request): string
    {
        $imposedName = $request->name
            ? sprintf('Use exactly this card name: "%s". ', $request->name)
            : 'Invent an original, evocative card name. ';

        return sprintf(
            '%sThe card is of type "%s" and rarity "%s". '
            .'Return JSON with keys "name" (string) and "description" '
            .'(one or two sentences, French, in-universe flavour text).',
            $imposedName,
            $request->cardType->name,
            $request->rarity->name,
        );
    }
}
