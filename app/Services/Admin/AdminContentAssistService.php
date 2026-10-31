<?php

namespace App\Services\Admin;

use App\Models\Store;
use App\Services\Ai\OpenAiClient;
use Throwable;

class AdminContentAssistService
{
    public function __construct(private OpenAiClient $openai) {}

    /**
     * @param  array<string, mixed>  $context
     * @return array{fields: array<string, string>, tips: list<string>, source: string}
     */
    public function suggest(Store $store, string $resource, array $context = []): array
    {
        $resource = in_array($resource, ['product', 'category', 'brand', 'coupon', 'settings', 'blog', 'banner'], true)
            ? $resource
            : 'product';

        if (config('ai.enabled') && $this->openai->configured()) {
            try {
                $raw = $this->openai->chat([
                    [
                        'role' => 'system',
                        'content' => $this->systemPrompt($store, $resource),
                    ],
                    [
                        'role' => 'user',
                        'content' => 'CONTEXT_JSON:\n'.json_encode([
                            'store' => [
                                'name' => $store->name,
                                'slug' => $store->slug,
                                'currency' => $store->currency,
                                'description' => $store->description,
                            ],
                            'resource' => $resource,
                            'draft' => $context,
                        ], JSON_UNESCAPED_UNICODE),
                    ],
                ], [
                    'temperature' => 0.5,
                    'max_tokens' => 900,
                ]);

                $parsed = $this->parse($raw);
                if ($parsed['fields'] !== []) {
                    return [...$parsed, 'source' => 'openai'];
                }
            } catch (Throwable $e) {
                report($e);
            }
        }

        return [...$this->heuristic($store, $resource, $context), 'source' => 'heuristic'];
    }

    private function systemPrompt(Store $store, string $resource): string
    {
        return <<<PROMPT
You are a merchandising copy assistant for {$store->name} inside Tanui Commerce (demonstration storefront).
Fill missing or weak draft fields for a {$resource} record.
Return STRICT JSON only:
{"fields":{"field_name":"value"},"tips":["short tip"]}
Only include fields the operator can paste into the form. Keep copy polished, concise, and brand-fit.
Never invent real payment claims. Use sample/demo tone when relevant.
PROMPT;
    }

    /**
     * @return array{fields: array<string, string>, tips: list<string>}
     */
    private function parse(string $raw): array
    {
        if (preg_match('/\{.*\}/s', $raw, $m)) {
            $raw = $m[0];
        }
        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            return ['fields' => [], 'tips' => []];
        }

        $fields = [];
        foreach (($decoded['fields'] ?? []) as $key => $value) {
            if (is_scalar($value)) {
                $fields[(string) $key] = trim((string) $value);
            }
        }

        return [
            'fields' => $fields,
            'tips' => array_values(array_filter(array_map('strval', $decoded['tips'] ?? []))),
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array{fields: array<string, string>, tips: list<string>}
     */
    private function heuristic(Store $store, string $resource, array $context): array
    {
        $name = trim((string) ($context['name'] ?? $context['title'] ?? $context['code'] ?? ''));
        $seed = $name !== '' ? $name : $store->name;

        return match ($resource) {
            'category' => [
                'fields' => [
                    'name' => $name !== '' ? $name : 'Featured picks',
                    'description' => "Curated {$seed} pieces for the {$store->name} demonstration catalogue. Use this aisle to highlight seasonal favourites and easy discovery paths.",
                ],
                'tips' => ['Keep category names shopper-short.', 'Description should explain what belongs here.'],
            ],
            'brand' => [
                'fields' => [
                    'name' => $name !== '' ? $name : 'Maison Sample',
                    'description' => "{$seed} is a demonstration house brand in {$store->name}, focused on dependable quality and clean merchandising stories.",
                ],
                'tips' => ['Brand copy should feel editorial, not technical.'],
            ],
            'coupon' => [
                'fields' => [
                    'code' => strtoupper(preg_replace('/[^A-Z0-9]/', '', substr(strtoupper($seed), 0, 8)) ?: 'WELCOME10'),
                    'discount_type' => 'percentage',
                    'discount_value' => '10',
                    'minimum_amount' => '2500',
                    'status' => 'active',
                ],
                'tips' => ['Pair a clear code with a minimum order so margins stay intentional.'],
            ],
            'settings' => [
                'fields' => [
                    'tagline' => $store->setting('tagline') ?: "Demonstration storefront for {$store->name}",
                    'description' => $store->description ?: "{$store->name} is a Tanui Commerce demo shop built for training, analytics and AI-assisted merchandising.",
                    'footer_text' => 'Demonstration demonstration storefront. Prices and fulfilment are simulated.',
                ],
                'tips' => ['Keep tagline one line.', 'Footer should remind visitors this is a demo.'],
            ],
            'blog' => [
                'fields' => [
                    'title' => $name !== '' ? $name : "Inside {$store->name}: this season’s edit",
                    'excerpt' => "A short editorial note from {$store->name} on what to explore next in the demonstration catalogue.",
                    'body' => "<p>Welcome to the {$store->name} journal. This sample post shows how store news can support discovery, explain collections and guide shoppers without leaving the storefront.</p><p>Use AI suggestions to draft titles, excerpts and body copy, then refine the voice for your brand.</p><p>In a live operation you would link featured products, announce drops and share care guides. Here, the post is demonstration content for Tanui Commerce.</p>",
                    'status' => 'published',
                ],
                'tips' => ['Lead with a concrete benefit.', 'Keep the first paragraph under 40 words.'],
            ],
            'banner' => [
                'fields' => [
                    'title' => $name !== '' ? $name : 'New season, open to explore',
                    'subtitle' => 'Demonstration merchandising banner for the storefront hero.',
                    'link' => '/shop',
                ],
                'tips' => ['Hero titles work best under 6 words.'],
            ],
            default => [
                'fields' => [
                    'name' => $name !== '' ? $name : 'Sample catalogue piece',
                    'short_description' => "A polished demonstration product for {$store->name}, ready for merchandising tests.",
                    'description' => "<p>{$seed} is part of the {$store->name} demo catalogue. Use it to rehearse product storytelling, pricing experiments and AI-assisted copy workflows.</p><p>Details are illustrative. Stock, fulfilment and payments remain simulated inside Tanui Commerce.</p>",
                    'status' => 'published',
                ],
                'tips' => ['Short description sells the click.', 'Long description can carry care and fit notes.'],
            ],
        };
    }
}
