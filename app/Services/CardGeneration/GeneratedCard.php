<?php

namespace App\Services\CardGeneration;

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
