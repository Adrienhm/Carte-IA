<?php

namespace Tests\Feature;

use App\Models\UserPack;
use App\Services\PackOpeningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\GameTestHelpers;
use Tests\TestCase;

class PackOpeningTest extends TestCase
{
    use RefreshDatabase;
    use GameTestHelpers;

    public function test_opening_a_pack_distributes_the_right_number_of_cards(): void
    {
        $user = $this->makeUser();
        $card = $this->makeCard();
        $pack = $this->makePack([$card->id => 10], cardsPerPack: 5);
        UserPack::create(['user_id' => $user->id, 'pack_id' => $pack->id]);

        $obtained = app(PackOpeningService::class)->open($user, $pack);

        $this->assertCount(5, $obtained);
        $this->assertSame(5, $user->cards()->count());
    }

    public function test_opening_consumes_exactly_one_pack(): void
    {
        $user = $this->makeUser();
        $card = $this->makeCard();
        $pack = $this->makePack([$card->id => 10]);
        UserPack::create(['user_id' => $user->id, 'pack_id' => $pack->id]);
        UserPack::create(['user_id' => $user->id, 'pack_id' => $pack->id]);

        app(PackOpeningService::class)->open($user, $pack);

        $this->assertSame(1, $user->packs()->count());
    }

    public function test_cannot_open_a_pack_you_do_not_own(): void
    {
        $user = $this->makeUser();
        $card = $this->makeCard();
        $pack = $this->makePack([$card->id => 10]);

        $this->expectException(RuntimeException::class);

        try {
            app(PackOpeningService::class)->open($user, $pack);
        } finally {
            // Aucune carte distribuee malgre l'echec (atomicite).
            $this->assertSame(0, $user->cards()->count());
        }
    }

    public function test_pack_opening_is_recorded(): void
    {
        $user = $this->makeUser();
        $card = $this->makeCard();
        $pack = $this->makePack([$card->id => 10]);
        UserPack::create(['user_id' => $user->id, 'pack_id' => $pack->id]);

        app(PackOpeningService::class)->open($user, $pack);

        $this->assertDatabaseHas('pack_openings', [
            'user_id' => $user->id,
            'pack_id' => $pack->id,
        ]);
    }

    public function test_only_configured_cards_can_be_drawn(): void
    {
        $user = $this->makeUser();
        $inPack = $this->makeCard();
        $notInPack = $this->makeCard();
        $pack = $this->makePack([$inPack->id => 10]);
        UserPack::create(['user_id' => $user->id, 'pack_id' => $pack->id]);

        app(PackOpeningService::class)->open($user, $pack);

        $this->assertSame(0, $user->cards()->where('card_id', $notInPack->id)->count());
    }
}
