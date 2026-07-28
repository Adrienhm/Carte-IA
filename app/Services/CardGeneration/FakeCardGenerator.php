<?php

namespace App\Services\CardGeneration;

class FakeCardGenerator implements CardGenerator
{
    /** @var list<string> */
    private array $prefixes = [
        'Gardien', 'Seigneur', 'Fureur', 'Sentinelle', 'Ombre', 'Aurore',
        'Rempart', 'Serment', 'Colosse', 'Faucon', 'Braise', 'Tonnerre',
    ];

    /** @var list<string> */
    private array $suffixes = [
        'de Fer', 'des Nations', 'du Nord', 'de Sang', 'des Cimes', 'oublie',
        'eternel', 'des Ruines', 'de la Frontiere', 'insoumis', 'des Cendres',
    ];

    public function __construct(
        private readonly PromptBuilder $prompts,
        private readonly StatRoller $stats,
    ) {
    }

    public function generate(CardGenerationRequest $request): GeneratedCard
    {
        $name = $request->name ?: $this->inventName();
        $stats = $this->stats->roll($request->rarity);

        $description = sprintf(
            'Une carte %s de type %s. %s au service de sa nation, forgee dans '
            .'les conflits de NationsGlory.',
            strtolower($request->rarity->name),
            strtolower($request->cardType->name),
            $name,
        );

        return new GeneratedCard(
            name: $name,
            description: $description,
            power: $stats['power'],
            defense: $stats['defense'],
            imagePrompt: $this->prompts->imagePrompt($request, $name),
            imageContents: $this->drawImage($name, $request->rarity->color, $request->cardType->name),
            imageExtension: 'png',
        );
    }

    private function inventName(): string
    {
        return $this->prefixes[array_rand($this->prefixes)]
            .' '.$this->suffixes[array_rand($this->suffixes)];
    }

    private function drawImage(string $name, string $hexColor, string $type): ?string
    {
        if (! function_exists('imagecreatetruecolor')) {
            return null;
        }

        $width = 512;
        $height = 512;
        $img = imagecreatetruecolor($width, $height);

        [$r, $g, $b] = $this->hexToRgb($hexColor);

        for ($y = 0; $y < $height; $y++) {
            $ratio = $y / $height;
            $lr = (int) (($r * $ratio) + (18 * (1 - $ratio)));
            $lg = (int) (($g * $ratio) + (18 * (1 - $ratio)));
            $lb = (int) (($b * $ratio) + (28 * (1 - $ratio)));
            $line = imagecolorallocate($img, $lr, $lg, $lb);
            imageline($img, 0, $y, $width, $y, $line);
        }

        $frame = imagecolorallocate($img, min($r + 40, 255), min($g + 40, 255), min($b + 40, 255));
        imagesetthickness($img, 6);
        imagerectangle($img, 8, 8, $width - 9, $height - 9, $frame);

        $white = imagecolorallocate($img, 245, 245, 245);
        $shadow = imagecolorallocate($img, 0, 0, 0);

        $this->drawCentered($img, 5, $type, $width, 60, $white, $shadow);
        $this->drawCentered($img, 5, $name, $width, 430, $white, $shadow);

        ob_start();
        imagepng($img);
        $contents = ob_get_clean();
        imagedestroy($img);

        return $contents ?: null;
    }

    /**
     * @param \GdImage $img
     */
    private function drawCentered($img, int $font, string $text, int $width, int $y, int $color, int $shadow): void
    {
        $charW = imagefontwidth($font);
        $x = (int) max(10, ($width - (strlen($text) * $charW)) / 2);
        imagestring($img, $font, $x + 1, $y + 1, $text, $shadow);
        imagestring($img, $font, $x, $y, $text, $color);
    }

    /**
     * @return array{0:int,1:int,2:int}
     */
    private function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        if (strlen($hex) !== 6) {
            return [120, 120, 130];
        }

        return [
            (int) hexdec(substr($hex, 0, 2)),
            (int) hexdec(substr($hex, 2, 2)),
            (int) hexdec(substr($hex, 4, 2)),
        ];
    }
}
