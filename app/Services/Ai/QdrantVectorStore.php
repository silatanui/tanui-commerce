<?php

namespace App\Services\Ai;

use App\Services\Ai\Contracts\VectorStore;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class QdrantVectorStore implements VectorStore
{
    public function ensureReady(): void
    {
        $collection = config('ai.vector.collection');
        $dimensions = (int) config('ai.openai.embedding_dimensions');

        $exists = $this->client()->get("/collections/{$collection}");

        if ($exists->successful()) {
            return;
        }

        $created = $this->client()->put("/collections/{$collection}", [
            'vectors' => [
                'size' => $dimensions,
                'distance' => 'Cosine',
            ],
        ]);

        if (! $created->successful()) {
            throw new RuntimeException('Unable to create Qdrant collection: '.$created->body());
        }
    }

    public function upsert(string $externalId, int $storeId, string $title, string $content, array $vector, array $metadata = []): void
    {
        $collection = config('ai.vector.collection');
        $pointId = $this->pointId($externalId);

        $response = $this->client()->put("/collections/{$collection}/points", [
            'points' => [[
                'id' => $pointId,
                'vector' => array_values($vector),
                'payload' => [
                    'external_id' => $externalId,
                    'store_id' => $storeId,
                    'title' => $title,
                    'content' => $content,
                    'metadata' => $metadata,
                ],
            ]],
        ]);

        if (! $response->successful()) {
            throw new RuntimeException('Qdrant upsert failed: '.$response->body());
        }
    }

    public function search(int $storeId, array $vector, int $limit, float $minScore): array
    {
        $collection = config('ai.vector.collection');

        $response = $this->client()->post("/collections/{$collection}/points/search", [
            'vector' => array_values($vector),
            'limit' => $limit,
            'score_threshold' => $minScore,
            'with_payload' => true,
            'filter' => [
                'must' => [[
                    'key' => 'store_id',
                    'match' => ['value' => $storeId],
                ]],
            ],
        ]);

        if (! $response->successful()) {
            throw new RuntimeException('Qdrant search failed: '.$response->body());
        }

        $hits = $response->json('result') ?? [];
        $out = [];

        foreach ($hits as $hit) {
            $payload = $hit['payload'] ?? [];
            $out[] = [
                'id' => (string) ($payload['external_id'] ?? $hit['id']),
                'score' => (float) ($hit['score'] ?? 0),
                'title' => (string) ($payload['title'] ?? ''),
                'content' => (string) ($payload['content'] ?? ''),
                'metadata' => is_array($payload['metadata'] ?? null) ? $payload['metadata'] : [],
            ];
        }

        return $out;
    }

    public function deleteByStore(int $storeId): void
    {
        $collection = config('ai.vector.collection');

        $this->client()->post("/collections/{$collection}/points/delete", [
            'filter' => [
                'must' => [[
                    'key' => 'store_id',
                    'match' => ['value' => $storeId],
                ]],
            ],
        ])->throw();
    }

    public function driverName(): string
    {
        return 'qdrant';
    }

    private function pointId(string $externalId): string
    {
        // Stable UUID-like hex id from content key (Qdrant accepts unsigned int or UUID)
        $hex = substr(hash('sha256', $externalId), 0, 32);

        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12)
        );
    }

    private function client(): PendingRequest
    {
        $request = Http::baseUrl(config('ai.qdrant.url'))
            ->acceptJson()
            ->timeout((int) config('ai.qdrant.timeout'))
            ->asJson();

        if (filled(config('ai.qdrant.api_key'))) {
            $request = $request->withHeaders(['api-key' => config('ai.qdrant.api_key')]);
        }

        return $request;
    }
}
