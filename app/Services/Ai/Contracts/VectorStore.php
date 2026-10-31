<?php

namespace App\Services\Ai\Contracts;

interface VectorStore
{
    public function ensureReady(): void;

    /** @param  list<float>  $vector */
    public function upsert(string $externalId, int $storeId, string $title, string $content, array $vector, array $metadata = []): void;

    /**
     * @param  list<float>  $vector
     * @return list<array{id:string,score:float,title:string,content:string,metadata:array}>
     */
    public function search(int $storeId, array $vector, int $limit, float $minScore): array;

    public function deleteByStore(int $storeId): void;

    public function driverName(): string;
}
