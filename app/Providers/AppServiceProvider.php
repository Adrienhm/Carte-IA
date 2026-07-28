<?php

namespace App\Providers;

use App\Services\CardGeneration\CardGenerationException;
use App\Services\CardGeneration\CardGenerator;
use App\Services\CardGeneration\FakeCardGenerator;
use App\Services\CardGeneration\OpenAiCardGenerator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CardGenerator::class, function ($app) {
            $driver = config('cards.driver');

            return match ($driver) {
                'openai' => $app->make(OpenAiCardGenerator::class),
                'fake' => $app->make(FakeCardGenerator::class),
                default => throw new CardGenerationException("Driver de generation inconnu : {$driver}"),
            };
        });
    }

    public function boot(): void
    {
    }
}
