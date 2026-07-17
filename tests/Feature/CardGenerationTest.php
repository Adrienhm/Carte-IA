<?php

namespace Tests\Feature;

use App\Services\CardGeneration\CardComposer;
use App\Services\CardGeneration\CardGenerationRequest;
use App\Services\CardGeneration\CardGenerator;
use App\Services\CardGeneration\FakeCardGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\GameTestHelpers;
use Tests\TestCase;

class CardGenerationTest extends TestCase
{
    use RefreshDatabase;
    use GameTestHelpers;

    public function test_fake_driver_is_used_in_tests(): void
    {
        $this->assertInstanceOf(FakeCardGenerator::class, app(CardGenerator::class));
    }

    public function test_generated_stats_respect_rarity_bounds(): void
    {
        $rarity = $this->makeRarity(['min_stat' => 70, 'max_stat' => 90]);
        $type = $this->makeType();

        $generator = app(CardGenerator::class);

        for ($i = 0; $i < 30; $i++) {
            $card = $generator->generate(new CardGenerationRequest($type, $rarity));
            $this->assertGreaterThanOrEqual(70, $card->power);
            $this->assertLessThanOrEqual(90, $card->power);
            $this->assertGreaterThanOrEqual(70, $card->defense);
            $this->assertLessThanOrEqual(90, $card->defense);
        }
    }

    public function test_composer_persists_image_and_sets_rarity_value(): void
    {
        Storage::fake('public');

        $rarity = $this->makeRarity(['base_value' => 250]);
        $type = $this->makeType();

        $card = app(CardComposer::class)->compose(new CardGenerationRequest($type, $rarity));
        $card->save();

        // Valeur par defaut = grille de la rarete (CDC 7.2).
        $this->assertSame(250, $card->value);
        $this->assertTrue($card->is_ai_generated);

        // L'illustration a bien ete stockee localement (CDC 9.3).
        $this->assertNotNull($card->image_path);
        Storage::disk('public')->assertExists($card->image_path);
    }

    public function test_imposed_name_is_respected(): void
    {
        $card = app(CardGenerator::class)->generate(
            new CardGenerationRequest($this->makeType(), $this->makeRarity(), 'Nom Impose')
        );

        $this->assertSame('Nom Impose', $card->name);
    }
}
