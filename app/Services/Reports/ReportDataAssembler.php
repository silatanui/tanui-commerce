<?php

namespace App\Services\Reports;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use App\Services\AnalyticsService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportDataAssembler
{
    public function __construct(
        private AnalyticsService $analytics,
        private ReportChartRenderer $charts,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function assemble(Store $store, string $type, Carbon $from, Carbon $to): array
    {
        $type = in_array($type, ['company', 'products', 'customers'], true) ? $type : 'company';
        $analytics = $this->analytics->dashboard($store, $from, $to);
        $accent = $store->primary_color ?: '#c45a3a';
        $ink = '#1a1a1a';
        $palette = [$accent, '#1a1a1a', '#6b4a3a', '#8d8d8d', '#d4a090', '#4a4a4a'];

        $meta = [
            'type' => $type,
            'title' => match ($type) {
                'products' => 'Product Performance Report',
                'customers' => 'Customer Intelligence Report',
                default => 'Company Performance Report',
            },
            'subtitle' => match ($type) {
                'products' => 'Catalogue, bestsellers, stock risk and assortment outlook',
                'customers' => 'Audience quality, loyalty signals and retention opportunities',
                default => 'Executive brief covering sales, customers, products and outlook',
            },
            'store' => $store,
            'from' => $from,
            'to' => $to,
            'generated_at' => now(),
            'accent' => $accent,
            'ink' => $ink,
            'currency' => $store->currency,
            'logo_path' => $this->localImagePath($store->logo ? public_path($store->logo) : public_path('demo-assets/stores/'.$store->slug.'-mark.svg')),
            'cover_image' => $this->coverImage($store),
        ];

        $projections = $this->buildProjections($analytics['trend'] ?? []);
        $decisionNotes = $this->decisionNotes($type, $analytics, $store);

        $common = [
            'meta' => $meta,
            'analytics' => $analytics,
            'projections' => $projections,
            'decision_notes' => $decisionNotes,
            'charts' => [
                'trend' => $this->charts->lineSvg(
                    collect($analytics['trend'] ?? [])->map(fn ($r) => ['label' => $r['label'], 'value' => $r['sales']])->all(),
                    collect($projections['points'] ?? [])->map(fn ($r) => ['label' => $r['label'], 'value' => $r['sales']])->all(),
                    $ink,
                    $accent
                ),
                'orders_trend' => $this->charts->lineSvg(
                    collect($analytics['trend'] ?? [])->map(fn ($r) => ['label' => $r['label'], 'value' => $r['orders']])->all(),
                    collect($projections['points'] ?? [])->map(fn ($r) => ['label' => $r['label'], 'value' => $r['orders']])->all(),
                    $ink,
                    $accent
                ),
                'categories' => $this->charts->barSvg(
                    collect($analytics['categories'] ?? [])->take(6)->map(fn ($r) => [
                        'label' => (string) data_get($r, 'category_name', 'Category'),
                        'value' => (float) data_get($r, 'revenue', 0),
                    ])->all(),
                    $accent
                ),
                'statuses' => $this->charts->donutSvg(
                    collect($analytics['order_statuses'] ?? [])->map(fn ($r) => [
                        'label' => (string) ($r['status'] ?? 'status'),
                        'value' => (float) ($r['total'] ?? 0),
                    ])->all(),
                    $palette
                ),
                'devices' => $this->charts->donutSvg(
                    collect($analytics['devices'] ?? [])->map(fn ($r) => [
                        'label' => (string) ($r['label'] ?? 'Device'),
                        'value' => (float) ($r['share'] ?? 0),
                    ])->all(),
                    $palette
                ),
            ],
            'toc' => $this->tocFor($type),
        ];

        return match ($type) {
            'products' => $common + $this->productsPayload($store, $from, $to, $analytics),
            'customers' => $common + $this->customersPayload($store, $from, $to, $analytics),
            default => $common + $this->companyPayload($store, $from, $to, $analytics),
        };
    }

    /**
     * @param  array<string, mixed>  $analytics
     * @return array<string, mixed>
     */
    private function companyPayload(Store $store, Carbon $from, Carbon $to, array $analytics): array
    {
        return [
            'sections' => ['executive', 'sales', 'products', 'customers', 'operations', 'geography', 'outlook'],
            'products_block' => $this->productSpotlight($store, $from, $to, 8),
            'customers_block' => $this->customerSpotlight($store, $from, $to, 10),
            'orders_block' => $this->recentOrders($store, $from, $to, 12),
            'catalogue' => [],
        ];
    }

    /**
     * @param  array<string, mixed>  $analytics
     * @return array<string, mixed>
     */
    private function productsPayload(Store $store, Carbon $from, Carbon $to, array $analytics): array
    {
        $catalogue = Product::query()
            ->where('store_id', $store->id)
            ->with(['images', 'category', 'brand', 'variants'])
            ->orderByDesc('is_featured')
            ->orderBy('name')
            ->limit(24)
            ->get()
            ->map(fn (Product $product) => [
                'name' => $product->name,
                'sku' => $product->sku,
                'status' => $product->status,
                'price' => (float) $product->price,
                'category' => $product->category?->name,
                'brand' => $product->brand?->name,
                'stock' => $product->availableStock(),
                'featured' => (bool) $product->is_featured,
                'image' => $this->productImagePath($product),
            ]);

        return [
            'sections' => ['executive', 'catalogue', 'bestsellers', 'stock', 'outlook'],
            'products_block' => $this->productSpotlight($store, $from, $to, 12),
            'catalogue' => $catalogue,
            'customers_block' => [],
            'orders_block' => [],
        ];
    }

    /**
     * @param  array<string, mixed>  $analytics
     * @return array<string, mixed>
     */
    private function customersPayload(Store $store, Carbon $from, Carbon $to, array $analytics): array
    {
        return [
            'sections' => ['executive', 'audience', 'loyalty', 'geography', 'outlook'],
            'customers_block' => $this->customerSpotlight($store, $from, $to, 20),
            'products_block' => $this->productSpotlight($store, $from, $to, 6),
            'orders_block' => $this->recentOrders($store, $from, $to, 10),
            'catalogue' => [],
        ];
    }

    /**
     * @return list<array{name: string, page: string}>
     */
    private function tocFor(string $type): array
    {
        return match ($type) {
            'products' => [
                ['name' => 'Cover & document control', 'page' => '01'],
                ['name' => 'Table of contents', 'page' => '02'],
                ['name' => 'Executive product brief', 'page' => '03'],
                ['name' => 'Catalogue gallery', 'page' => '04'],
                ['name' => 'Bestsellers & category mix', 'page' => '05'],
                ['name' => 'Stock risk register', 'page' => '06'],
                ['name' => 'Assortment outlook & projections', 'page' => '07'],
            ],
            'customers' => [
                ['name' => 'Cover & document control', 'page' => '01'],
                ['name' => 'Table of contents', 'page' => '02'],
                ['name' => 'Audience executive brief', 'page' => '03'],
                ['name' => 'Customer leaderboard', 'page' => '04'],
                ['name' => 'Loyalty & retention signals', 'page' => '05'],
                ['name' => 'Geographic origins (Kenya)', 'page' => '06'],
                ['name' => 'Growth outlook', 'page' => '07'],
            ],
            default => [
                ['name' => 'Cover & document control', 'page' => '01'],
                ['name' => 'Table of contents', 'page' => '02'],
                ['name' => 'Executive summary', 'page' => '03'],
                ['name' => 'Sales performance & projections', 'page' => '04'],
                ['name' => 'Product highlights', 'page' => '05'],
                ['name' => 'Customer intelligence', 'page' => '06'],
                ['name' => 'Operations & fulfilment', 'page' => '07'],
                ['name' => 'Kenya client origins', 'page' => '08'],
                ['name' => 'Decision outlook', 'page' => '09'],
            ],
        };
    }

    /**
     * @param  list<array<string, mixed>>  $trend
     * @return array<string, mixed>
     */
    private function buildProjections(array $trend): array
    {
        $rows = collect($trend);
        if ($rows->count() < 3) {
            return ['points' => [], 'sales_next' => 0, 'orders_next' => 0, 'growth_pct' => 0, 'confidence' => 'low'];
        }

        $recent = $rows->slice(-7)->values();
        $prior = $rows->slice(-14, 7)->values();
        $recentSales = (float) $recent->avg('sales');
        $priorSales = (float) max(1, $prior->avg('sales') ?: $recentSales);
        $growth = (($recentSales - $priorSales) / $priorSales);
        $recentOrders = (float) $recent->avg('orders');

        $points = [];
        $cursor = Carbon::parse((string) $rows->last()['label'])->addDay();
        // Labels may be like "Oct 12" - fallback to sequential days
        try {
            $cursor = Carbon::createFromFormat('M j', (string) $rows->last()['label'])->year(now()->year)->addDay();
        } catch (\Throwable) {
            $cursor = now()->addDay();
        }

        for ($i = 1; $i <= 7; $i++) {
            $factor = 1 + ($growth * (0.55 + ($i * 0.05)));
            $points[] = [
                'label' => $cursor->format('M j'),
                'sales' => round(max(0, $recentSales * $factor), 2),
                'orders' => (int) max(0, round($recentOrders * $factor)),
            ];
            $cursor->addDay();
        }

        return [
            'points' => $points,
            'sales_next' => round(collect($points)->sum('sales'), 2),
            'orders_next' => (int) collect($points)->sum('orders'),
            'growth_pct' => round($growth * 100, 1),
            'confidence' => abs($growth) > 0.35 ? 'moderate' : 'stable',
        ];
    }

    /**
     * @param  array<string, mixed>  $analytics
     * @return list<string>
     */
    private function decisionNotes(string $type, array $analytics, Store $store): array
    {
        $notes = [];
        $changes = $analytics['changes'] ?? [];
        $notes[] = ((float) ($changes['sales'] ?? 0) >= 0)
            ? 'Sales momentum is positive versus the prior window — protect conversion quality while scaling paid or featured placements.'
            : 'Sales softened versus the prior window — tighten fulfilment and refresh the top three SKUs before expanding acquisition spend.';

        if (($analytics['pending'] ?? 0) > 0) {
            $notes[] = 'There are open fulfilment orders. Clearing the pipeline will improve customer trust metrics faster than adding new SKUs.';
        }

        if (count($analytics['low_stock'] ?? []) > 0) {
            $notes[] = 'Low-stock variants are present. Prioritise replenishment for items already converting in this period.';
        }

        $topCounty = data_get($analytics, 'geo.top_counties.0.name');
        if ($topCounty) {
            $notes[] = "Simulated Kenya traffic peaks in {$topCounty}. Align campaigns and delivery promises to that county cluster first.";
        }

        if ($type === 'customers') {
            $notes[] = 'Treat returning-visitor share as a loyalty proxy: design a simple post-purchase nudge for first-time buyers.';
        }

        if ($type === 'products') {
            $notes[] = 'Featured products should mirror the bestsellers table; misalignment usually signals merchandising drift.';
        }

        $notes[] = $store->name.' remains a demonstration storefront — use these figures for decision rehearsal, not live financial filing.';

        return $notes;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function productSpotlight(Store $store, Carbon $from, Carbon $to, int $limit): array
    {
        $popular = OrderItem::query()
            ->select('product_id', 'product_name', DB::raw('SUM(quantity) as units'), DB::raw('SUM(subtotal) as revenue'))
            ->whereHas('order', function ($query) use ($store, $from, $to) {
                $query->where('store_id', $store->id)
                    ->where('status', '!=', 'cancelled')
                    ->whereBetween('created_at', [$from, $to]);
            })
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('units')
            ->limit($limit)
            ->get();

        return $popular->map(function ($row) use ($store) {
            $product = $row->product_id
                ? Product::query()->with(['images', 'category'])->where('store_id', $store->id)->find($row->product_id)
                : Product::query()->with(['images', 'category'])->where('store_id', $store->id)->where('name', $row->product_name)->first();

            return [
                'name' => $row->product_name,
                'units' => (int) $row->units,
                'revenue' => (float) $row->revenue,
                'image' => $product ? $this->productImagePath($product) : null,
                'price' => $product ? (float) $product->price : null,
                'category' => $product?->category?->name,
            ];
        })->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function customerSpotlight(Store $store, Carbon $from, Carbon $to, int $limit): array
    {
        $orders = Order::query()
            ->where('store_id', $store->id)
            ->where('status', '!=', 'cancelled')
            ->whereBetween('created_at', [$from, $to])
            ->with('user')
            ->get();

        $grouped = [];
        foreach ($orders as $order) {
            $key = $order->user_id
                ? 'u:'.$order->user_id
                : 'e:'.strtolower((string) data_get($order->customer_details, 'email', $order->order_number));
            if (! isset($grouped[$key])) {
                $grouped[$key] = [
                    'name' => data_get($order->customer_details, 'full_name') ?: ($order->user?->name ?? 'Guest'),
                    'email' => data_get($order->customer_details, 'email') ?: ($order->user?->email ?? '—'),
                    'orders' => 0,
                    'spend' => 0.0,
                    'last_order' => $order->created_at,
                ];
            }
            $grouped[$key]['orders']++;
            $grouped[$key]['spend'] += (float) $order->total;
            if ($order->created_at->gt($grouped[$key]['last_order'])) {
                $grouped[$key]['last_order'] = $order->created_at;
            }
        }

        return collect($grouped)
            ->sortByDesc('spend')
            ->take($limit)
            ->map(fn ($row) => [
                'name' => $row['name'],
                'email' => $row['email'],
                'orders' => $row['orders'],
                'spend' => $row['spend'],
                'last_order' => $row['last_order']?->toDateString(),
                'tier' => $row['orders'] >= 3 ? 'Loyal' : ($row['orders'] === 2 ? 'Returning' : 'New'),
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function recentOrders(Store $store, Carbon $from, Carbon $to, int $limit): array
    {
        return Order::query()
            ->where('store_id', $store->id)
            ->whereBetween('created_at', [$from, $to])
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn (Order $order) => [
                'number' => $order->order_number,
                'customer' => data_get($order->customer_details, 'full_name') ?: 'Guest',
                'total' => (float) $order->total,
                'status' => $order->status,
                'date' => optional($order->created_at)->toDateString(),
            ])
            ->all();
    }

    private function productImagePath(Product $product): ?string
    {
        $image = $product->relationLoaded('images')
            ? ($product->images->firstWhere('is_primary', true) ?? $product->images->first())
            : null;

        if (! $image) {
            return null;
        }

        $path = $image->image_path;
        if (str_starts_with((string) $path, 'demo-assets/')) {
            return $this->localImagePath(public_path($path));
        }

        return $this->localImagePath(storage_path('app/public/'.$path));
    }

    private function coverImage(Store $store): ?string
    {
        $product = Product::query()
            ->where('store_id', $store->id)
            ->where('status', 'published')
            ->with('images')
            ->orderByDesc('is_featured')
            ->first();

        return $product ? $this->productImagePath($product) : null;
    }

    private function localImagePath(?string $absolute): ?string
    {
        if (! $absolute || ! is_file($absolute)) {
            return null;
        }

        // Dompdf prefers file:// on Windows with forward slashes.
        return 'file:///' . str_replace('\\', '/', $absolute);
    }
}
