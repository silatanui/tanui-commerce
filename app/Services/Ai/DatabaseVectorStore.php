<?php

namespace App\Services\Ai;

use App\Models\AiDocument;
use App\Services\Ai\Contracts\VectorStore;

class DatabaseVectorStore implements VectorStore
{
    public function ensureReady(): void
    {
        // Table created by migration — nothing to provision.
    }

    public function upsert(string $externalId, int $storeId, string $title, string $content, array $vector, array $metadata = []): void
    {
        AiDocument::query()->updateOrCreate(
            ['external_id' => $externalId],
            [
                'store_id' => $storeId,
                'source_type' => $metadata['source_type'] ?? 'product',
                'source_id' => $metadata['source_id'] ?? null,
                'title' => $title,
                'content' => $content,
                'content_hash' => hash('sha256', $content),
                'embedding' => json_encode($vector, JSON_THROW_ON_ERROR),
                'metadata' => $metadata,
            ]
        );
    }

    public function search(int $storeId, array $vector, int $limit, float $minScore): array
    {
        $docs = AiDocument::query()
            ->where('store_id', $storeId)
            ->whereNotNull('embedding')
            ->get();

        $scored = [];

        foreach ($docs as $doc) {
            $candidate = json_decode((string) $doc->embedding, true);
            if (! is_array($candidate) || $candidate === []) {
                continue;
            }

            $score = $this->cosine($vector, $candidate);
            if ($score < $minScore) {
                continue;
            }

            $scored[] = [
                'id' => $doc->external_id,
                'score' => $score,
                'title' => $doc->title,
                'content' => $doc->content,
                'metadata' => $doc->metadata ?? [],
            ];
        }

        usort($scored, fn ($a, $b) => $b['score'] <=> $a['score']);

        return array_slice($scored, 0, $limit);
    }

    public function deleteByStore(int $storeId): void
    {
        AiDocument::query()->where('store_id', $storeId)->delete();
    }

    public function driverName(): string
    {
        return 'database';
    }

    /** @param  list<float>  $a @param  list<float>  $b */
    private function cosine(array $a, array $b): float
    {
        $n = min(count($a), count($b));
        if ($n === 0) {
            return 0.0;
        }

        $dot = 0.0;
        $na = 0.0;
        $nb = 0.0;

        for ($i = 0; $i < $n; $i++) {
            $dot += $a[$i] * $b[$i];
            $na += $a[$i] * $a[$i];
            $nb += $b[$i] * $b[$i];
        }

        if ($na <= 0.0 || $nb <= 0.0) {
            return 0.0;
        }

        return $dot / (sqrt($na) * sqrt($nb));
    }
}
