<?php

namespace App\Services\Ai;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAiClient
{
    public function configured(): bool
    {
        return filled(config('ai.openai.api_key'));
    }

    public function embed(string $text): array
    {
        $response = $this->client()->post('/embeddings', [
            'model' => config('ai.openai.embedding_model'),
            'input' => $text,
        ])->throw()->json();

        $vector = $response['data'][0]['embedding'] ?? null;

        if (! is_array($vector) || $vector === []) {
            throw new RuntimeException('OpenAI embedding response did not include a vector.');
        }

        return $vector;
    }

    public function chat(array $messages): string
    {
        $response = $this->client()->post('/chat/completions', [
            'model' => config('ai.openai.chat_model'),
            'temperature' => config('ai.openai.temperature'),
            'max_tokens' => config('ai.openai.max_tokens'),
            'messages' => $messages,
        ])->throw()->json();

        $content = $response['choices'][0]['message']['content'] ?? null;

        if (! is_string($content) || trim($content) === '') {
            throw new RuntimeException('OpenAI chat response was empty.');
        }

        return trim($content);
    }

    private function client(): PendingRequest
    {
        if (! $this->configured()) {
            throw new RuntimeException('OPENAI_API_KEY is missing. Add it to your .env file.');
        }

        return Http::baseUrl(config('ai.openai.base_url'))
            ->withToken(config('ai.openai.api_key'))
            ->acceptJson()
            ->timeout((int) config('ai.openai.timeout'))
            ->asJson();
    }
}
