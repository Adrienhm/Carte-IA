<?php

namespace App\Services\CardGeneration;

use App\Models\Card;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Orchestre la generation d'une carte : appelle le driver IA, stocke
 * localement l'illustration recue (CDC 9.3 "images stockees localement pour
 * eviter les appels repetes") et prepare les attributs du modele Card.
 *
 * Le composer ne persiste PAS la carte en base : il rend un modele non
 * sauvegarde, laissant le controleur decider (creation ou previsualisation).
 */
class CardComposer
{
    public function __construct(private readonly CardGenerator $generator)
    {
    }

    public function compose(CardGenerationRequest $request): Card
    {
        $generated = $this->generator->generate($request);

        $imagePath = $this->storeImage($generated);

        $card = new Card([
            'name' => $generated->name,
            'description' => $generated->description,
            'card_type_id' => $request->cardType->id,
            'rarity_id' => $request->rarity->id,
            // Valeur par defaut = grille de la rarete (CDC 7.2), ajustable ensuite.
            'value' => $request->rarity->base_value,
            'power' => $generated->power,
            'defense' => $generated->defense,
            'image_path' => $imagePath,
            'is_ai_generated' => true,
            'image_prompt' => $generated->imagePrompt,
            'is_active' => true,
        ]);

        return $card;
    }

    private function storeImage(GeneratedCard $generated): ?string
    {
        if ($generated->imageContents === null) {
            return null;
        }

        $disk = config('cards.image_disk');
        $dir = trim(config('cards.image_dir'), '/');
        $filename = $dir.'/'.Str::uuid()->toString().'.'.$generated->imageExtension;

        Storage::disk($disk)->put($filename, $generated->imageContents);

        return $filename;
    }
}
