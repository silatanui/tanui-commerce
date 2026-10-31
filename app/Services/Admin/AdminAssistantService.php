<?php

namespace App\Services\Admin;

use App\Models\Store;
use App\Services\Ai\OpenAiClient;
use App\Services\AnalyticsService;
use Carbon\Carbon;
use Throwable;

class AdminAssistantService
{
    public function __construct(
        private AnalyticsService $analytics,
        private OpenAiClient $openai,
    ) {}

    /**
     * @param  list<array{role: string, content: string}>  $history
     * @return array{
     *     answer: string,
     *     actions: list<string>,
     *     tables: list<array<string, mixed>>,
     *     charts: list<array<string, mixed>>,
     *     configured: bool,
     *     source: string
     * }
     */
    public function ask(Store $store, string $question, array $history = [], ?Carbon $from = null, ?Carbon $to = null): array
    {
        $from ??= now()->subDays(29)->startOfDay();
        $to ??= now()->endOfDay();
        $report = $this->analytics->dashboard($store, $from, $to);
        $context = $this->compactReport($store, $report);

        if (! config('ai.enabled') || ! $this->openai->configured()) {
            $fallback = $this->heuristicAnswer($question, $context);

            return [...$fallback, 'configured' => false, 'source' => 'heuristic'];
        }

        try {
            $messages = [
                ['role' => 'system', 'content' => $this->systemPrompt($store)],
                ['role' => 'system', 'content' => 'STORE_PERFORMANCE_JSON:\n'.json_encode($context, JSON_UNESCAPED_UNICODE)],
            ];

            $maxHistory = (int) config('ai.admin_assistant.max_history', 8);
            foreach (array_slice($history, -$maxHistory) as $turn) {
                $role = ($turn['role'] ?? '') === 'assistant' ? 'assistant' : 'user';
                $content = trim((string) ($turn['content'] ?? ''));
                if ($content !== '') {
                    $messages[] = ['role' => $role, 'content' => $content];
                }
            }

            $messages[] = ['role' => 'user', 'content' => trim($question)];

            $raw = $this->openai->chat($messages, [
                'temperature' => (float) config('ai.admin_assistant.temperature', 0.35),
                'max_tokens' => (int) config('ai.admin_assistant.max_tokens', 1600),
            ]);

            $parsed = $this->parseStructuredReply($raw, $context);

            return [...$parsed, 'configured' => true, 'source' => 'openai'];
        } catch (Throwable $e) {
            report($e);
            $fallback = $this->heuristicAnswer($question, $context);
            $fallback['answer'] = "Live model request failed, so here is a local analytics summary instead.\n\n".$fallback['answer'];

            return [...$fallback, 'configured' => true, 'source' => 'heuristic-fallback'];
        }
    }

    /**
     * @param  array<string, mixed>  $report
     * @return array<string, mixed>
     */
    private function compactReport(Store $store, array $report): array
    {
        $geo = $report['geo'] ?? [];

        return [
            'store' => [
                'name' => $store->name,
                'slug' => $store->slug,
                'currency' => $store->currency,
            ],
            'period' => [
                'from' => optional($report['from'])->toDateString(),
                'to' => optional($report['to'])->toDateString(),
            ],
            'kpis' => [
                'sales' => $report['sales'] ?? 0,
                'orders' => $report['orders'] ?? 0,
                'average_order' => $report['average'] ?? 0,
                'customers' => $report['customers'] ?? 0,
                'sessions' => $report['interactions'] ?? 0,
                'conversion_pct' => $report['conversion'] ?? 0,
                'returning_share_pct' => $report['returning_share'] ?? 0,
                'products_published' => $report['products'] ?? 0,
                'pending_orders' => $report['pending'] ?? 0,
                'pending_reviews' => $report['pending_reviews'] ?? 0,
            ],
            'changes_pct' => $report['changes'] ?? [],
            'devices' => $report['devices'] ?? [],
            'order_statuses' => $report['order_statuses'] ?? [],
            'trend' => collect($report['trend'] ?? [])->map(fn ($row) => [
                'label' => $row['label'] ?? '',
                'sales' => $row['sales'] ?? 0,
                'orders' => $row['orders'] ?? 0,
                'target' => $row['target'] ?? 0,
            ])->values()->all(),
            'categories' => $this->rows($report['categories'] ?? [], ['category_name', 'revenue', 'units']),
            'channels' => $this->rows($report['channels'] ?? [], ['name', 'share', 'units']),
            'top_products' => $this->rows($report['popular'] ?? [], ['product_name', 'units', 'revenue']),
            'low_stock' => collect($report['low_stock'] ?? [])->take(8)->map(function ($variant) {
                return [
                    'product' => $variant->product->name ?? 'Product',
                    'variant' => method_exists($variant, 'label') ? $variant->label() : '',
                    'stock' => (int) ($variant->stock_quantity ?? 0),
                ];
            })->values()->all(),
            'geo' => [
                'country' => $geo['country'] ?? 'Kenya',
                'total_sessions' => $geo['total_sessions'] ?? 0,
                'top_counties' => array_slice($geo['top_counties'] ?? $geo['counties'] ?? [], 0, 10),
            ],
        ];
    }

    /**
     * @param  iterable<mixed>  $items
     * @param  list<string>  $keys
     * @return list<array<string, mixed>>
     */
    private function rows(iterable $items, array $keys): array
    {
        return collect($items)->take(10)->map(function ($item) use ($keys) {
            $row = [];
            foreach ($keys as $key) {
                $row[$key] = data_get($item, $key);
            }

            return $row;
        })->values()->all();
    }

    private function systemPrompt(Store $store): string
    {
        return <<<PROMPT
You are the Tanui Admin performance assistant for {$store->name}.
You help store operators understand demonstration analytics and make clear operational decisions.

Rules:
- Use ONLY the STORE_PERFORMANCE_JSON context. Do not invent metrics.
- Be decisive: recommend concrete next actions (inventory, fulfilment, marketing focus, assortment).
- Prefer short paragraphs, then bullets for actions.
- Geography is simulated Kenya county data; say so when discussing geo.
- Money values are demonstration prices in the store currency.
- Respond with STRICT JSON only (no markdown fences) using this schema:
{
  "answer": "plain text insight for the operator",
  "actions": ["decision 1", "decision 2"],
  "tables": [{"title":"...", "headers":["..."], "rows":[["..."]]}],
  "charts": [{"title":"...","type":"line|bar|doughnut","labels":["..."],"datasets":[{"label":"...","data":[0]}]}]
}
- Include at least one table or chart when the question asks for stats, comparison, trend, ranking, or performance.
- Keep charts to at most 2 and tables to at most 2.
- Chart data must come from the JSON context (trend, categories, devices, statuses, counties, products).
PROMPT;
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array{answer: string, actions: list<string>, tables: list<array<string, mixed>>, charts: list<array<string, mixed>>}
     */
    private function parseStructuredReply(string $raw, array $context): array
    {
        $json = $raw;
        if (preg_match('/\{.*\}/s', $raw, $matches)) {
            $json = $matches[0];
        }

        $decoded = json_decode($json, true);
        if (! is_array($decoded)) {
            return $this->heuristicAnswer('summary', $context, $raw);
        }

        return [
            'answer' => trim((string) ($decoded['answer'] ?? $raw)),
            'actions' => array_values(array_filter(array_map('strval', $decoded['actions'] ?? []))),
            'tables' => $this->normalizeTables($decoded['tables'] ?? []),
            'charts' => $this->normalizeCharts($decoded['charts'] ?? []),
        ];
    }

    /**
     * @param  mixed  $tables
     * @return list<array{title: string, headers: list<string>, rows: list<list<string>>}>
     */
    private function normalizeTables(mixed $tables): array
    {
        if (! is_array($tables)) {
            return [];
        }

        $out = [];
        foreach (array_slice($tables, 0, 2) as $table) {
            if (! is_array($table)) {
                continue;
            }
            $headers = array_values(array_map('strval', $table['headers'] ?? []));
            $rows = [];
            foreach (($table['rows'] ?? []) as $row) {
                if (is_array($row)) {
                    $rows[] = array_values(array_map(fn ($cell) => is_scalar($cell) ? (string) $cell : json_encode($cell), $row));
                }
            }
            $out[] = [
                'title' => (string) ($table['title'] ?? 'Table'),
                'headers' => $headers,
                'rows' => $rows,
            ];
        }

        return $out;
    }

    /**
     * @param  mixed  $charts
     * @return list<array{title: string, type: string, labels: list<string>, datasets: list<array{label: string, data: list<float|int>}}}>
     */
    private function normalizeCharts(mixed $charts): array
    {
        if (! is_array($charts)) {
            return [];
        }

        $out = [];
        foreach (array_slice($charts, 0, 2) as $chart) {
            if (! is_array($chart)) {
                continue;
            }
            $type = strtolower((string) ($chart['type'] ?? 'bar'));
            if (! in_array($type, ['line', 'bar', 'doughnut'], true)) {
                $type = 'bar';
            }
            $datasets = [];
            foreach (($chart['datasets'] ?? []) as $dataset) {
                if (! is_array($dataset)) {
                    continue;
                }
                $datasets[] = [
                    'label' => (string) ($dataset['label'] ?? 'Series'),
                    'data' => array_values(array_map(fn ($n) => is_numeric($n) ? 0 + $n : 0, $dataset['data'] ?? [])),
                ];
            }
            $out[] = [
                'title' => (string) ($chart['title'] ?? 'Chart'),
                'type' => $type,
                'labels' => array_values(array_map('strval', $chart['labels'] ?? [])),
                'datasets' => $datasets,
            ];
        }

        return $out;
    }

    /**
     * Local decision summary when the model is unavailable.
     *
     * @param  array<string, mixed>  $context
     * @return array{answer: string, actions: list<string>, tables: list<array<string, mixed>>, charts: list<array<string, mixed>>}
     */
    private function heuristicAnswer(string $question, array $context, ?string $prefix = null): array
    {
        $kpis = $context['kpis'] ?? [];
        $changes = $context['changes_pct'] ?? [];
        $currency = $context['store']['currency'] ?? '';
        $q = strtolower($question);

        $answer = sprintf(
            "For %s (%s to %s), simulated sales are %s %s across %s orders. Conversion is %s%% with %s pending fulfilment items and %s low-stock variants needing attention.",
            $context['store']['name'] ?? 'this store',
            $context['period']['from'] ?? '—',
            $context['period']['to'] ?? '—',
            number_format((float) ($kpis['sales'] ?? 0), 2),
            $currency,
            number_format((int) ($kpis['orders'] ?? 0)),
            $kpis['conversion_pct'] ?? 0,
            number_format((int) ($kpis['pending_orders'] ?? 0)),
            count($context['low_stock'] ?? [])
        );

        if (str_contains($q, 'kenya') || str_contains($q, 'county') || str_contains($q, 'geo')) {
            $top = collect($context['geo']['top_counties'] ?? [])->take(3)->pluck('name')->implode(', ');
            $answer .= ' Simulated Kenya traffic concentrates in '.$top.'.';
        }

        if ($prefix) {
            $answer = trim($prefix)."\n\n".$answer;
        }

        $actions = [
            ((float) ($changes['sales'] ?? 0) < 0)
                ? 'Prioritise a recovery push on top categories before expanding assortment.'
                : 'Double down on the current top category and county clusters while sales momentum is positive.',
            ((int) ($kpis['pending_orders'] ?? 0) > 0)
                ? 'Clear open fulfilment first; pending orders are diluting conversion quality.'
                : 'Keep fulfilment SLA tight and watch returning-visitor share weekly.',
            count($context['low_stock'] ?? []) > 0
                ? 'Restock the listed low-stock variants before the next promo window.'
                : 'Inventory depth looks stable; schedule a mid-period stock audit anyway.',
        ];

        $productRows = collect($context['top_products'] ?? [])->map(fn ($row) => [
            (string) ($row['product_name'] ?? ''),
            (string) ($row['units'] ?? 0),
            (string) ($row['revenue'] ?? 0),
        ])->values()->all();

        $trend = collect($context['trend'] ?? []);
        $charts = [[
            'title' => 'Revenue trend',
            'type' => 'line',
            'labels' => $trend->pluck('label')->all(),
            'datasets' => [[
                'label' => 'Sales',
                'data' => $trend->pluck('sales')->map(fn ($n) => 0 + $n)->all(),
            ]],
        ]];

        if (str_contains($q, 'device')) {
            $devices = collect($context['devices'] ?? []);
            $charts = [[
                'title' => 'Device split',
                'type' => 'doughnut',
                'labels' => $devices->pluck('label')->all(),
                'datasets' => [[
                    'label' => 'Share',
                    'data' => $devices->pluck('share')->map(fn ($n) => 0 + $n)->all(),
                ]],
            ]];
        }

        return [
            'answer' => $answer,
            'actions' => $actions,
            'tables' => [[
                'title' => 'Top products',
                'headers' => ['Product', 'Units', 'Revenue'],
                'rows' => $productRows,
            ]],
            'charts' => $charts,
        ];
    }
}
