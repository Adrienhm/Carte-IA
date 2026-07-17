<?php

namespace Tests\Unit;

use App\Services\WeightedDrawService;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class WeightedDrawServiceTest extends TestCase
{
    private WeightedDrawService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new WeightedDrawService();
    }

    public function test_it_only_returns_candidates_with_positive_weight(): void
    {
        // 'b' a un poids nul : il ne doit jamais sortir.
        $weights = ['a' => 5, 'b' => 0, 'c' => 5];

        for ($i = 0; $i < 200; $i++) {
            $this->assertNotSame('b', $this->service->drawKey($weights));
        }
    }

    public function test_distribution_roughly_follows_weights(): void
    {
        // 90/10 : sur un grand nombre de tirages, 'commune' doit largement
        // dominer. On verifie l'ordre de grandeur, pas une valeur exacte.
        $weights = ['commune' => 90, 'rare' => 10];
        $draws = $this->service->drawMany($weights, 5000);

        $counts = array_count_values($draws);
        $communeRatio = $counts['commune'] / 5000;

        $this->assertGreaterThan(0.82, $communeRatio);
        $this->assertLessThan(0.98, $communeRatio);
    }

    public function test_it_rejects_empty_candidates(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service->drawKey([]);
    }

    public function test_it_rejects_zero_total_weight(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service->drawKey(['a' => 0, 'b' => 0]);
    }

    public function test_it_rejects_negative_weight(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service->drawKey(['a' => -1]);
    }

    public function test_draw_many_returns_requested_count(): void
    {
        $result = $this->service->drawMany(['a' => 1, 'b' => 1], 7);
        $this->assertCount(7, $result);
    }
}
