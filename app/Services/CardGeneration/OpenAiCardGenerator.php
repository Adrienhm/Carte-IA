<?php

namespace App\Services\CardGeneration;

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Log;
use Throwable;

class OpenAiCardGenerator implements CardGenerator
{
    public function __construct(
        private readonly HttpFactory $http,
        private readonly PromptBuilder $prompts,
        private readonly StatRoller $stats,
    ) {
    }

    public function generate(CardGenerationRequest $request): GeneratedCard
    {
        if (empty(config('cards.openai.api_key'))) {
            throw new CardGenerationException(
                "Cle OpenAI absente : renseignez OPENAI_API_KEY dans le .env ou "
                ."basculez CARD_AI_DRIVER=fake."
            );
        }

        [$name, $description] = $this->generateText($request);

        $imagePrompt = $this->prompts->imagePrompt($request, $name);
        $imageContents = $this->generateImage($imagePrompt);

        $rolledStats = $this->stats->roll($request->rarity);

        return new GeneratedCard(
            name: $name,
            description: $description,
            power: $rolledStats['power'],
            defense: $rolledStats['defense'],
            imagePrompt: $imagePrompt,
            imageContents: $imageContents,
            imageExtension: 'png',
        );
    }

    /**
     * @return array{0:string,1:string}
     */
    private function generateText(CardGenerationRequest $request): array
    {
        $response = $this->request()->post('/chat/completions', [
            'model' => config('cards.openai.text_model'),
            'messages' => [
                ['role' => 'system', 'content' => $this->prompts->textSystemPrompt()],
                ['role' => 'user', 'content' => $this->prompts->textUserPrompt($request)],
            ],
            'response_format' => ['type' => 'json_object'],
            'temperature' => 0.9,
        ]);

        if ($response->failed()) {
            throw new CardGenerationException(
                'API texte OpenAI : '.$this->describeError($response->status(), $response->body())
            );
        }

        $content = data_get($response->json(), 'choices.0.message.content');
        $data = json_decode((string) $content, true);

        $name = is_array($data) ? ($data['name'] ?? null) : null;
        $description = is_array($data) ? ($data['description'] ?? null) : null;

        $name = $request->name ?: $name;

        if (! is_string($name) || $name === '' || ! is_string($description) || $description === '') {
            throw new CardGenerationException('Reponse texte OpenAI inexploitable (JSON incomplet).');
        }

        return [trim($name), trim($description)];
    }

    private function generateImage(string $prompt): string
    {
        $response = $this->request()->post('/images/generations', [
            'model' => config('cards.openai.image_model'),
            'prompt' => $prompt,
            'n' => 1,
            'size' => config('cards.openai.image_size'),
            'response_format' => 'b64_json',
        ]);

        if ($response->failed()) {
            throw new CardGenerationException(
                'API image OpenAI : '.$this->describeError($response->status(), $response->body())
            );
        }

        $b64 = data_get($response->json(), 'data.0.b64_json');
        $binary = is_string($b64) ? base64_decode($b64, true) : false;

        if ($binary === false || $binary === '') {
            throw new CardGenerationException("Image OpenAI illisible (base64 invalide).");
        }

        return $binary;
    }

    private function request(): PendingRequest
    {
        return $this->http
            ->baseUrl(config('cards.openai.base_uri'))
            ->withToken(config('cards.openai.api_key'))
            ->timeout(config('cards.openai.timeout'))
            ->retry(config('cards.openai.max_retries'), 1000, function (Throwable $e) {
                Log::warning('Nouvel essai de generation IA', ['erreur' => $e->getMessage()]);

                return true;
            }, throw: false);
    }

    private function describeError(int $status, string $body): string
    {
        return match (true) {
            $status === 401 => 'cle invalide (401).',
            $status === 429 => 'quota depasse ou trop de requetes (429).',
            $status >= 500 => "service indisponible ($status), reessayez plus tard.",
            default => "erreur $status : ".mb_strimwidth($body, 0, 200, '...'),
        };
    }
}
