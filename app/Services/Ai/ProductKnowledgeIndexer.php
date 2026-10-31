<?php

namespace App\Services\Ai;

use App\Models\Product;
use App\Models\Store;
use App\Services\Ai\Contracts\VectorStore;
use Illuminate\Support\Collection;

class ProductKnowledgeIndexer
{
    public function __construct(
        private OpenAiClient $openai,
        private TextSanitizer $sanitizer,
        private VectorStore $vectors,
    ) {}

    public function reindexStore(Store $store, bool $fresh = false): int
    {
        $this->vectors->ensureReady();

        if ($fresh) {
            $this->vectors->deleteByStore($store->id);
        }

        $count = 0;
        $catalogueLines = [];

        $store->products()
            ->with(['category', 'brand', 'variants'])
            ->published()
            ->orderBy('id')
            ->chunkById(25, function (Collection $products) use ($store, &$count, &$catalogueLines) {
                foreach ($products as $product) {
                    $this->indexProduct($store, $product);
                    $catalogueLines[] = trim(sprintf(
                        '- %s (%s) sample price %s %s%s',
                        $product->name,
                        $product->category?->name ?? 'General',
                        $product->price,
                        $store->currency,
                        $product->brand?->name ? ', brand '.$product->brand->name : ''
                    ));
                    $count++;
                }
            });

        foreach ($this->storePolicyChunks($store, $catalogueLines) as $chunk) {
            $this->upsertChunk(
                store: $store,
                externalId: $chunk['external_id'],
                title: $chunk['title'],
                content: $chunk['content'],
                metadata: $chunk['metadata'],
            );
            $count++;
        }

        return $count;
    }

    public function indexProduct(Store $store, Product $product): void
    {
        $product->loadMissing(['category', 'brand', 'variants']);

        $specs = '';
        if (is_array($product->specifications)) {
            foreach ($product->specifications as $key => $value) {
                $specs .= "{$key}: {$value}. ";
            }
        }

        $variantBits = $product->variants
            ->map(fn ($v) => trim(($v->sku ?? '').' '.json_encode($v->attributes ?? [])))
            ->filter()
            ->implode('; ');

        $raw = trim(implode("\n", array_filter([
            "Product: {$product->name}",
            $product->brand?->name ? "Brand: {$product->brand->name}" : null,
            $product->category?->name ? "Category: {$product->category->name}" : null,
            "SKU: {$product->sku}",
            "Sample price: {$product->price} {$store->currency}",
            $product->compare_at_price ? "Compare-at sample price: {$product->compare_at_price}" : null,
            $product->short_description,
            $product->description,
            $specs ? "Specifications: {$specs}" : null,
            $variantBits ? "Variants: {$variantBits}" : null,
            'This is a demonstration catalogue item. No real purchase or payment is processed.',
        ])));

        $this->upsertChunk(
            store: $store,
            externalId: "store-{$store->id}-product-{$product->id}",
            title: $product->name,
            content: $raw,
            metadata: [
                'source_type' => 'product',
                'source_id' => $product->id,
                'slug' => $product->slug,
            ],
        );
    }

    private function upsertChunk(Store $store, string $externalId, string $title, string $content, array $metadata): void
    {
        $sanitized = $this->sanitizer->sanitize($content);
        if ($sanitized === '') {
            return;
        }

        $vector = $this->openai->embed($sanitized);

        $this->vectors->upsert(
            externalId: $externalId,
            storeId: $store->id,
            title: $title,
            content: $this->sanitizer->sanitizeDisplay($content),
            vector: $vector,
            metadata: $metadata,
        );
    }

    /**
     * @param  list<string>  $catalogueLines
     * @return list<array{external_id:string,title:string,content:string,metadata:array}>
     */
    private function storePolicyChunks(Store $store, array $catalogueLines): array
    {
        $list = $catalogueLines !== []
            ? implode("\n", $catalogueLines)
            : 'No published products are currently indexed.';

        return [
            [
                'external_id' => "store-{$store->id}-catalogue-overview",
                'title' => "What {$store->name} sells",
                'content' => "Customers often ask what we sell, what products are available, show me the catalogue, or browse recommendations. {$store->name} sells these demonstration products:\n{$list}\nTagline: ".$store->setting('tagline', '').'. All prices are sample demonstration prices.',
                'metadata' => ['source_type' => 'catalogue', 'source_id' => null],
            ],
            [
                'external_id' => "store-{$store->id}-policy-demo",
                'title' => 'Demonstration store policy',
                'content' => "{$store->name} is a fictional retailer inside the Tanui Commerce portfolio. Checkout is simulated. No real payments are taken. Delivery and returns text is demonstration-only. Tagline: ".$store->setting('tagline', ''),
                'metadata' => ['source_type' => 'policy', 'source_id' => null],
            ],
            [
                'external_id' => "store-{$store->id}-policy-contact",
                'title' => 'Store contact information',
                'content' => "Contact for {$store->name}: email ".$store->setting('email', 'n/a').', phone '.$store->setting('phone', 'n/a').', address '.$store->setting('address', 'n/a').'.',
                'metadata' => ['source_type' => 'policy', 'source_id' => null],
            ],
        ];
    }
}
