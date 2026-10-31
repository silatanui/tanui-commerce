<?php

namespace App\Services\Ai;

use App\Models\AiChatMessage;
use App\Models\AiChatSession;
use App\Models\Product;
use App\Models\Store;
use App\Services\Ai\Contracts\VectorStore;
use Illuminate\Support\Str;
use Throwable;

class RagChatbotService
{
    public function __construct(
        private OpenAiClient $openai,
        private TextSanitizer $sanitizer,
        private VectorStore $vectors,
    ) {}

    /**
     * @return array{
     *   answer:string,
     *   sources:list<array{title:string,score:float}>,
     *   products:list<array<string,mixed>>,
     *   latency_ms:int,
     *   driver:string,
     *   session_token:?string,
     *   model:string
     * }
     */
    public function ask(Store $store, string $rawQuestion, ?string $sessionToken = null, ?int $userId = null, ?string $ip = null): array
    {
        if (! config('ai.enabled')) {
            return $this->fallback('The AI assistant is disabled in configuration.', [], [], 0);
        }

        if (! $this->openai->configured()) {
            return $this->fallback(
                'AI is not configured yet. Add OPENAI_API_KEY to the server .env file, then run php artisan ai:reindex.',
                [],
                [],
                0
            );
        }

        $started = hrtime(true);
        $displayQuestion = $this->sanitizer->sanitizeDisplay($rawQuestion);
        $sanitizedQuestion = $this->sanitizer->sanitize($rawQuestion);

        if ($sanitizedQuestion === '' || mb_strlen($displayQuestion) < 2) {
            return $this->fallback('Please ask a short product or store question.', [], [], 0);
        }

        $session = $this->resolveSession($store, $sessionToken, $userId, $ip);
        $prior = $this->recentHistory($session);

        AiChatMessage::query()->create([
            'ai_chat_session_id' => $session->id,
            'role' => 'user',
            'content' => $displayQuestion,
            'raw_content' => $rawQuestion,
        ]);

        $minScore = $this->isBrowseIntent($sanitizedQuestion)
            ? min(0.28, (float) config('ai.vector.min_score'))
            : (float) config('ai.vector.min_score');

        try {
            $this->vectors->ensureReady();
            $embedding = $this->openai->embed($sanitizedQuestion);
            $hits = $this->vectors->search(
                storeId: $store->id,
                vector: $embedding,
                limit: max(8, (int) config('ai.vector.top_k')),
                minScore: $minScore,
            );
        } catch (Throwable $e) {
            report($e);

            return $this->persistAssistant(
                $session,
                'I could not search the store knowledge base right now. If you just set this up, run `php artisan ai:reindex` and ensure the vector driver is reachable.',
                [],
                [],
                $started
            );
        }

        if ($hits === []) {
            $featured = $this->featuredProducts($store, 4);
            if ($featured !== []) {
                $context = collect($featured)->map(fn ($p) => "{$p['name']} — {$p['price_label']} — {$p['short']}")->implode("\n");

                try {
                    $answer = $this->openai->chat([
                        ['role' => 'system', 'content' => str_replace('{store_name}', $store->name, (string) config('ai.chat.system_prompt'))],
                        ['role' => 'system', 'content' => "CONTEXT (featured catalogue):\n{$context}"],
                        ['role' => 'user', 'content' => $displayQuestion],
                    ]);
                } catch (Throwable $e) {
                    report($e);
                    $answer = 'Here are featured demonstration products from '.$store->name.'.';
                }

                return $this->persistAssistant($session, $answer, [], $featured, $started);
            }

            return $this->persistAssistant(
                $session,
                'I do not have enough grounded catalogue information for that yet. Ask an admin to run `php artisan ai:reindex --fresh`.',
                [],
                [],
                $started
            );
        }

        $contextBlocks = [];
        $sources = [];
        $productIds = [];

        foreach ($hits as $i => $hit) {
            $n = $i + 1;
            $contextBlocks[] = "[#{$n} score=".number_format($hit['score'], 3)."] {$hit['title']}\n{$hit['content']}";
            $sources[] = [
                'title' => $hit['title'],
                'score' => round($hit['score'], 3),
                'source_type' => $hit['metadata']['source_type'] ?? null,
            ];

            if (($hit['metadata']['source_type'] ?? null) === 'product' && ! empty($hit['metadata']['source_id'])) {
                $productIds[] = (int) $hit['metadata']['source_id'];
            }
        }

        // Browse intents: also attach featured/published products if few product hits
        if ($this->isBrowseIntent($sanitizedQuestion) && count($productIds) < 3) {
            $extra = Product::query()
                ->forStore($store)
                ->published()
                ->with(['images', 'brand', 'variants'])
                ->orderByDesc('is_featured')
                ->orderBy('name')
                ->limit(6)
                ->get();

            foreach ($extra as $product) {
                $productIds[] = $product->id;
            }
        }

        $products = $this->productCards($store, array_values(array_unique($productIds)));

        $system = str_replace(
            '{store_name}',
            $store->name,
            (string) config('ai.chat.system_prompt')
        ).' When recommending items, keep the text short; product cards are shown separately in the UI.';

        $messages = [
            ['role' => 'system', 'content' => $system],
            ['role' => 'system', 'content' => "CONTEXT:\n".implode("\n\n", $contextBlocks)],
        ];

        foreach ($prior as $row) {
            $messages[] = ['role' => $row->role, 'content' => $row->content];
        }

        $messages[] = ['role' => 'user', 'content' => $displayQuestion];

        try {
            $answer = $this->openai->chat($messages);
        } catch (Throwable $e) {
            report($e);

            return $this->persistAssistant(
                $session,
                'The language model request failed. Check OPENAI_API_KEY, model name, and network access.',
                $sources,
                $products,
                $started
            );
        }

        return $this->persistAssistant($session, $answer, $sources, $products, $started);
    }

    /** @return list<array<string,mixed>> */
    public function sessionSummaries(Store $store, array $tokens): array
    {
        $tokens = array_values(array_filter(array_unique($tokens)));
        if ($tokens === []) {
            return [];
        }

        $sessions = AiChatSession::query()
            ->where('store_id', $store->id)
            ->whereIn('session_token', $tokens)
            ->with(['messages' => fn ($q) => $q->orderBy('id')])
            ->latest('updated_at')
            ->get();

        return $sessions->map(function (AiChatSession $session) {
            $firstUser = $session->messages->firstWhere('role', 'user');
            $last = $session->messages->last();

            return [
                'session_token' => $session->session_token,
                'title' => Str::limit($firstUser?->content ?? 'New conversation', 42),
                'snippet' => Str::limit($last?->content ?? '', 64),
                'updated_at' => optional($session->updated_at)->toIso8601String(),
                'updated_label' => optional($session->updated_at)->diffForHumans(),
            ];
        })->values()->all();
    }

    /** @return list<array{role:string,content:string,products?:array,sources?:array}> */
    public function sessionMessages(Store $store, string $sessionToken): array
    {
        $session = AiChatSession::query()
            ->where('store_id', $store->id)
            ->where('session_token', $sessionToken)
            ->firstOrFail();

        return $session->messages()
            ->orderBy('id')
            ->get()
            ->map(fn (AiChatMessage $m) => [
                'role' => $m->role,
                'content' => $m->content,
                'sources' => $m->sources ?? [],
                'latency_ms' => $m->latency_ms,
            ])
            ->all();
    }

    private function isBrowseIntent(string $sanitized): bool
    {
        return (bool) preg_match(
            '/\b(what do you sell|what.*sell|catalogue|catalog|products?|recommend|show me|browse|available|collection|best seller|bestsellers?)\b/i',
            $sanitized
        );
    }

    private function resolveSession(Store $store, ?string $sessionToken, ?int $userId, ?string $ip): AiChatSession
    {
        if ($sessionToken) {
            $existing = AiChatSession::query()
                ->where('store_id', $store->id)
                ->where('session_token', $sessionToken)
                ->first();

            if ($existing) {
                $existing->touch();

                return $existing;
            }
        }

        return AiChatSession::query()->create([
            'store_id' => $store->id,
            'session_token' => Str::random(48),
            'user_id' => $userId,
            'ip_address' => $ip,
        ]);
    }

    /** @return \Illuminate\Support\Collection<int, AiChatMessage> */
    private function recentHistory(AiChatSession $session)
    {
        $limit = (int) config('ai.chat.max_history');

        return AiChatMessage::query()
            ->where('ai_chat_session_id', $session->id)
            ->whereIn('role', ['user', 'assistant'])
            ->latest('id')
            ->limit($limit)
            ->get()
            ->reverse()
            ->values();
    }

    /**
     * @param  list<int>  $ids
     * @return list<array<string,mixed>>
     */
    private function productCards(Store $store, array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $products = Product::query()
            ->forStore($store)
            ->published()
            ->whereIn('id', $ids)
            ->with(['images', 'brand', 'category', 'variants' => fn ($q) => $q->where('status', 'active')])
            ->get()
            ->sortBy(fn ($p) => array_search($p->id, $ids, true))
            ->take(4);

        return $products->map(fn (Product $product) => $this->mapProduct($store, $product))->values()->all();
    }

    /** @return list<array<string,mixed>> */
    private function featuredProducts(Store $store, int $limit): array
    {
        return Product::query()
            ->forStore($store)
            ->published()
            ->with(['images', 'brand', 'category', 'variants' => fn ($q) => $q->where('status', 'active')])
            ->orderByDesc('is_featured')
            ->orderBy('name')
            ->limit($limit)
            ->get()
            ->map(fn (Product $product) => $this->mapProduct($store, $product))
            ->all();
    }

    private function mapProduct(Store $store, Product $product): array
    {
        $variant = $product->variants->first();

        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'brand' => $product->brand?->name,
            'category' => $product->category?->name,
            'short' => Str::limit(strip_tags((string) ($product->short_description ?: $product->description)), 110),
            'price' => (float) $product->price,
            'price_label' => money($product->price, $store->currency),
            'compare_at' => $product->compare_at_price ? money($product->compare_at_price, $store->currency) : null,
            'image' => $product->primaryImageUrl(),
            'url' => route('store.product', [$store->slug, $product->slug]),
            'in_stock' => $product->availableStock() > 0,
            'rating' => $product->averageRating(),
            'variant_id' => $variant?->id,
            'specs' => is_array($product->specifications) ? $product->specifications : [],
        ];
    }

    /**
     * @param  list<array{title:string,score:float}>  $sources
     * @param  list<array<string,mixed>>  $products
     */
    private function persistAssistant(AiChatSession $session, string $answer, array $sources, array $products, int $started): array
    {
        $latency = (int) ((hrtime(true) - $started) / 1_000_000);

        AiChatMessage::query()->create([
            'ai_chat_session_id' => $session->id,
            'role' => 'assistant',
            'content' => $answer,
            'sources' => [
                'sources' => $sources,
                'products' => collect($products)->pluck('id')->all(),
            ],
            'latency_ms' => $latency,
        ]);

        return [
            'answer' => $answer,
            'sources' => $sources,
            'products' => $products,
            'latency_ms' => $latency,
            'driver' => $this->vectors->driverName(),
            'session_token' => $session->session_token,
            'model' => config('ai.openai.chat_model'),
        ];
    }

    /** @param  list<array{title:string,score:float}>  $sources @param  list<array<string,mixed>>  $products */
    private function fallback(string $answer, array $sources, array $products, int $latency): array
    {
        return [
            'answer' => $answer,
            'sources' => $sources,
            'products' => $products,
            'latency_ms' => $latency,
            'driver' => config('ai.vector.driver'),
            'session_token' => null,
            'model' => config('ai.openai.chat_model'),
        ];
    }
}
