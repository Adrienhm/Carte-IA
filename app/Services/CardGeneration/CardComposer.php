<?php

namespace App\Services\CardGeneration;

use App\Models\Card;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
