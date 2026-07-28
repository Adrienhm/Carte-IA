<?php

namespace App\Services\CardGeneration;

class PromptBuilder
{
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

    public function textSystemPrompt(): string
    {
        return 'You design collectible cards for '.config('cards.theme').'. '
            .'Answer only with strict JSON, no markdown, no commentary.';
    }

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
