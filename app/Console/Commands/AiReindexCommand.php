<?php

namespace App\Console\Commands;

use App\Models\Store;
use App\Services\Ai\OpenAiClient;
use App\Services\Ai\ProductKnowledgeIndexer;
use Illuminate\Console\Command;

class AiReindexCommand extends Command
{
    protected $signature = 'ai:reindex
        {store? : Store slug (fashion, electronics, home, market). Omit for all active stores.}
        {--fresh : Delete existing vectors for the store before indexing}';

    protected $description = 'Embed store catalogue + policy text into the RAG vector store (thesis pipeline).';

    public function handle(ProductKnowledgeIndexer $indexer, OpenAiClient $openai): int
    {
        if (! $openai->configured()) {
            $this->error('OPENAI_API_KEY is missing in .env');

            return self::FAILURE;
        }

        $slug = $this->argument('store');
        $fresh = (bool) $this->option('fresh');

        $stores = Store::query()
            ->when($slug, fn ($q) => $q->where('slug', $slug))
            ->where('status', 'active')
            ->get();

        if ($stores->isEmpty()) {
            $this->error('No matching active stores found.');

            return self::FAILURE;
        }

        $this->info('Vector driver: '.config('ai.vector.driver'));
        $this->info('Chat model: '.config('ai.openai.chat_model'));
        $this->info('Embedding model: '.config('ai.openai.embedding_model'));

        foreach ($stores as $store) {
            $this->line("Indexing {$store->slug}…");
            $count = $indexer->reindexStore($store, $fresh);
            $this->info("  → {$count} knowledge chunks upserted.");
        }

        $this->info('Done.');

        return self::SUCCESS;
    }
}
