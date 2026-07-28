<?php

namespace Tests\Feature;

use App\Models\Trade;
use App\Models\UserCard;
use App\Services\TradeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\GameTestHelpers;
use Tests\TestCase;

class TradeServiceTest extends TestCase
{
    use RefreshDatabase;
    use GameTestHelpers;

    private TradeService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(TradeService::class);
    }

    public function test_proposing_a_trade_locks_the_involved_cards(): void
    {
        $alice = $this->makeUser();
        $bob = $this->makeUser();
        $card = $this->makeCard();
        $aliceCard = $this->giveCard($alice, $card);
        $bobCard = $this->giveCard($bob, $card);

        $trade = $this->service->propose($alice, $bob, [$aliceCard->id], [$bobCard->id]);

        $this->assertSame($trade->id, $aliceCard->fresh()->locked_by_trade_id);
        $this->assertSame($trade->id, $bobCard->fresh()->locked_by_trade_id);
    }

    public function test_accepting_transfers_ownership_without_duplication(): void
    {
        $alice = $this->makeUser();
        $bob = $this->makeUser();
        $card = $this->makeCard();
        $aliceCard = $this->giveCard($alice, $card);
        $bobCard = $this->giveCard($bob, $card);

        $trade = $this->service->propose($alice, $bob, [$aliceCard->id], [$bobCard->id]);
        $this->service->accept($trade, $bob);

        $this->assertSame($bob->id, $aliceCard->fresh()->user_id);
        $this->assertSame($alice->id, $bobCard->fresh()->user_id);

        $this->assertSame(2, UserCard::count());

        $this->assertNull($aliceCard->fresh()->locked_by_trade_id);
        $this->assertNull($bobCard->fresh()->locked_by_trade_id);

        $this->assertSame(Trade::STATUS_ACCEPTED, $trade->fresh()->status);
    }

    public function test_only_the_receiver_can_accept(): void
    {
        $alice = $this->makeUser();
        $bob = $this->makeUser();
        $aliceCard = $this->giveCard($alice, $this->makeCard());

        $trade = $this->service->propose($alice, $bob, [$aliceCard->id], []);

        $this->expectException(RuntimeException::class);
        $this->service->accept($trade, $alice);
    }

    public function test_rejecting_releases_cards_and_keeps_ownership(): void
    {
        $alice = $this->makeUser();
        $bob = $this->makeUser();
        $aliceCard = $this->giveCard($alice, $this->makeCard());
        $bobCard = $this->giveCard($bob, $this->makeCard());

        $trade = $this->service->propose($alice, $bob, [$aliceCard->id], [$bobCard->id]);
        $this->service->reject($trade, $bob);

        $this->assertSame($alice->id, $aliceCard->fresh()->user_id);
        $this->assertSame($bob->id, $bobCard->fresh()->user_id);
        $this->assertNull($aliceCard->fresh()->locked_by_trade_id);
        $this->assertNull($bobCard->fresh()->locked_by_trade_id);
        $this->assertSame(Trade::STATUS_REJECTED, $trade->fresh()->status);
    }

    public function test_cannot_propose_a_card_already_locked_in_another_trade(): void
    {
        $alice = $this->makeUser();
        $bob = $this->makeUser();
        $charlie = $this->makeUser();
        $aliceCard = $this->giveCard($alice, $this->makeCard());

        $this->service->propose($alice, $bob, [$aliceCard->id], []);

        $this->expectException(RuntimeException::class);
        $this->service->propose($alice, $charlie, [$aliceCard->id], []);
    }

    public function test_cannot_propose_a_card_you_do_not_own(): void
    {
        $alice = $this->makeUser();
        $bob = $this->makeUser();
        $bobCard = $this->giveCard($bob, $this->makeCard());

        $this->expectException(RuntimeException::class);
        $this->service->propose($alice, $bob, [$bobCard->id], []);
    }

    public function test_a_pending_trade_cannot_be_accepted_twice(): void
    {
        $alice = $this->makeUser();
        $bob = $this->makeUser();
        $aliceCard = $this->giveCard($alice, $this->makeCard());

        $trade = $this->service->propose($alice, $bob, [$aliceCard->id], []);
        $this->service->accept($trade, $bob);

        $this->expectException(RuntimeException::class);
        $this->service->accept($trade, $bob);
    }
}
