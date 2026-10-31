<?php

namespace App\Services;

use App\Models\DemoSession;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\ProductVariant;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    public function dashboard(Store $store, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $from ??= now()->subDays(29)->startOfDay();
        $to ??= now()->endOfDay();

        $days = max(1, $from->diffInDays($to) + 1);
        $previousFrom = $from->copy()->subDays($days)->startOfDay();
        $previousTo = $from->copy()->subSecond();

        $current = $this->periodMetrics($store, $from, $to);
        $previous = $this->periodMetrics($store, $previousFrom, $previousTo);

        $threshold = (int) ($store->setting('low_stock_threshold') ?: config('commerce.low_stock_threshold'));

        $lowStock = ProductVariant::query()
            ->where('status', 'active')
            ->where('stock_quantity', '<=', $threshold)
            ->whereHas('product', fn ($query) => $query->where('store_id', $store->id)->where('status', 'published'))
            ->with('product')
            ->orderBy('stock_quantity')
            ->limit(8)
            ->get();

        $popular = OrderItem::query()
            ->select('product_name', DB::raw('SUM(quantity) as units'), DB::raw('SUM(subtotal) as revenue'))
            ->whereHas('order', function ($query) use ($store, $from, $to) {
                $query->where('store_id', $store->id)
                    ->where('status', '!=', 'cancelled')
                    ->whereBetween('created_at', [$from, $to]);
            })
            ->groupBy('product_name')
            ->orderByDesc('units')
            ->limit(6)
            ->get();

        $categories = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->leftJoin('products', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->where('orders.store_id', $store->id)
            ->where('orders.status', '!=', 'cancelled')
            ->whereBetween('orders.created_at', [$from, $to])
            ->selectRaw("COALESCE(categories.name, 'Uncategorised') as category_name, SUM(order_items.subtotal) as revenue, SUM(order_items.quantity) as units")
            ->groupByRaw("COALESCE(categories.name, 'Uncategorised')")
            ->orderByDesc('revenue')
            ->get();

        $categoryTotal = max(1, (float) $categories->sum('revenue'));
        $channels = $categories->take(3)->values()->map(function ($row) use ($categoryTotal, $store) {
            return [
                'name' => $row->category_name,
                'units' => (int) $row->units,
                'revenue' => (float) $row->revenue,
                'share' => round(((float) $row->revenue / $categoryTotal) * 100, 1),
                'formatted_revenue' => money($row->revenue, $store->currency),
            ];
        });

        $trend = [];
        $cursor = $from->copy()->startOfDay();
        $end = $to->copy()->startOfDay();
        $dailyTarget = round(($current['sales'] / max(1, $days)) * 1.12, 2);

        while ($cursor <= $end && count($trend) < 62) {
            $dayOrders = Order::query()
                ->where('store_id', $store->id)
                ->where('status', '!=', 'cancelled')
                ->whereDate('created_at', $cursor);

            $trend[] = [
                'label' => $cursor->format('d M'),
                'sales' => (float) (clone $dayOrders)->sum('total'),
                'orders' => (int) (clone $dayOrders)->count(),
                'target' => $dailyTarget,
            ];

            $cursor->addDay();
        }

        $movements = InventoryMovement::query()
            ->where('store_id', $store->id)
            ->with(['variant.product'])
            ->latest()
            ->limit(8)
            ->get()
            ->map(function (InventoryMovement $movement) use ($store) {
                $product = $movement->variant?->product;
                $outgoing = $movement->quantity < 0;

                return [
                    'title' => $product?->name ?? 'Catalogue item',
                    'subtitle' => $outgoing ? 'Outgoing stock' : 'Incoming stock',
                    'quantity' => abs((int) $movement->quantity),
                    'amount' => money(abs((int) $movement->quantity) * (float) ($product?->price ?? 0), $store->currency),
                    'when' => optional($movement->created_at)->diffForHumans() ?? '',
                    'outgoing' => $outgoing,
                    'type' => $movement->movement_type,
                ];
            });

        $heatmap = $this->orderHeatmap($store, $from, $to);
        $topProducts = $popular->take(3)->values();
        $geo = $this->kenyaClientOrigins($store, $current['interactions'], $current['orders'], $current['customers']);
        $orderStatuses = Order::query()
            ->where('store_id', $store->id)
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'status' => (string) $row->status,
                'total' => (int) $row->total,
            ])
            ->values()
            ->all();

        $conversion = $current['interactions'] > 0
            ? round(($current['orders'] / max(1, $current['interactions'])) * 100, 1)
            : 0.0;

        return [
            'from' => $from,
            'to' => $to,
            'sales' => $current['sales'],
            'orders' => $current['orders'],
            'average' => $current['average'],
            'products' => Product::query()->where('store_id', $store->id)->where('status', 'published')->count(),
            'customers' => $current['customers'],
            'interactions' => $current['interactions'],
            'conversion' => $conversion,
            'returning_share' => $geo['returning_share'],
            'devices' => $geo['devices'],
            'geo' => $geo,
            'order_statuses' => $orderStatuses,
            'pending' => Order::query()->where('store_id', $store->id)->whereIn('status', ['pending', 'confirmed', 'processing'])->count(),
            'pending_reviews' => ProductReview::query()
                ->where('status', 'pending')
                ->whereHas('product', fn ($query) => $query->where('store_id', $store->id))
                ->count(),
            'changes' => [
                'sales' => $this->percentChange($current['sales'], $previous['sales']),
                'orders' => $this->percentChange($current['orders'], $previous['orders']),
                'customers' => $this->percentChange($current['customers'], $previous['customers']),
                'interactions' => $this->percentChange($current['interactions'], $previous['interactions']),
            ],
            'low_stock' => $lowStock,
            'popular' => $popular,
            'top_products' => $topProducts,
            'categories' => $categories,
            'channels' => $channels,
            'channel_units' => (int) $categories->sum('units'),
            'recent' => Order::query()->where('store_id', $store->id)->latest()->limit(8)->get(),
            'movements' => $movements,
            'heatmap' => $heatmap,
            'trend' => $trend,
        ];
    }

    /**
     * Simulated Kenya visitor geography by county (choropleth) plus city hotspots.
     *
     * County names match public/geo/kenya-counties.geojson feature properties.name.
     *
     * @return array<string, mixed>
     */
    private function kenyaClientOrigins(Store $store, int $sessions, int $orders, int $customers): array
    {
        $seed = abs(crc32($store->slug.'-kenya-counties'));
        $totalSessions = max(220, $sessions > 0 ? max($sessions, 180) : 260 + ($seed % 120));
        // Keep KPI order totals separate; geography uses a richer demo pool so the map is readable.
        $geoOrders = max($orders, (int) round($totalSessions * (0.14 + (($seed % 6) / 100))));

        // Weights approximate online retail density; names must match GeoJSON.
        $countyWeights = [
            'Nairobi' => 96, 'Kiambu' => 48, 'Mombasa' => 42, 'Nakuru' => 34, 'Kajiado' => 28,
            'Machakos' => 24, 'Kisumu' => 22, 'Uasin Gishu' => 20, 'Kilifi' => 16, 'Meru' => 15,
            'Kakamega' => 14, 'Kisii' => 13, 'Nyeri' => 12, "Murang'a" => 12, 'Bungoma' => 11,
            'Kericho' => 10, 'Migori' => 9, 'Homa Bay' => 9, 'Trans Nzoia' => 9, 'Narok' => 8,
            'Nyandarua' => 8, 'Embu' => 7, 'Nandi' => 7, 'Kirinyaga' => 7, 'Siaya' => 7,
            'Bomet' => 6, 'Kwale' => 6, 'Laikipia' => 6, 'Nyamira' => 5, 'Busia' => 5,
            'Vihiga' => 5, 'Makueni' => 5, 'Kitui' => 5, 'Garissa' => 4, 'Baringo' => 4,
            'Elgeyo-Marakwet' => 4, 'West Pokot' => 3, 'Tharaka' => 3, 'Taita Taveta' => 3,
            'Lamu' => 3, 'Tana River' => 2, 'Isiolo' => 2, 'Samburu' => 2, 'Marsabit' => 2,
            'Wajir' => 2, 'Mandera' => 2, 'Turkana' => 2,
        ];

        $weightSum = array_sum($countyWeights);
        $allocated = 0;
        $counties = [];
        $index = 0;
        $names = array_keys($countyWeights);
        $last = count($names) - 1;

        foreach ($countyWeights as $name => $weight) {
            $share = $weight / $weightSum;
            $jitter = (($seed + ($index * 13)) % 9) - 4;
            $count = (int) max(1, round(($totalSessions * $share) + $jitter));
            if ($index === $last) {
                $count = max(1, $totalSessions - $allocated);
            }
            $allocated += $count;
            $countyOrders = (int) max(0, round($geoOrders * ($count / max(1, $totalSessions))));
            $counties[] = [
                'name' => $name,
                'sessions' => $count,
                'orders' => $countyOrders,
                'share' => round(($count / max(1, $totalSessions)) * 100, 1),
            ];
            $index++;
        }

        usort($counties, fn ($a, $b) => $b['sessions'] <=> $a['sessions']);
        $maxSessions = max(1, $counties[0]['sessions'] ?? 1);

        $hotspots = [
            ['name' => 'Nairobi', 'lat' => -1.286389, 'lng' => 36.817223, 'county' => 'Nairobi'],
            ['name' => 'Mombasa', 'lat' => -4.043477, 'lng' => 39.668206, 'county' => 'Mombasa'],
            ['name' => 'Kisumu', 'lat' => -0.091702, 'lng' => 34.767956, 'county' => 'Kisumu'],
            ['name' => 'Nakuru', 'lat' => -0.303099, 'lng' => 36.080025, 'county' => 'Nakuru'],
            ['name' => 'Eldoret', 'lat' => 0.514277, 'lng' => 35.269780, 'county' => 'Uasin Gishu'],
            ['name' => 'Thika', 'lat' => -1.033260, 'lng' => 37.069328, 'county' => 'Kiambu'],
            ['name' => 'Malindi', 'lat' => -3.217990, 'lng' => 40.119133, 'county' => 'Kilifi'],
        ];

        $byName = collect($counties)->keyBy('name');
        $mappedHotspots = [];
        foreach ($hotspots as $spot) {
            $county = $byName->get($spot['county']);
            $mappedHotspots[] = [
                'name' => $spot['name'],
                'lat' => $spot['lat'],
                'lng' => $spot['lng'],
                'county' => $spot['county'],
                'sessions' => (int) ($county['sessions'] ?? 0),
                'orders' => (int) ($county['orders'] ?? 0),
            ];
        }

        return [
            'country' => 'Kenya',
            'total_sessions' => $totalSessions,
            'max_sessions' => $maxSessions,
            'counties' => $counties,
            'top_counties' => array_slice($counties, 0, 10),
            'hotspots' => $mappedHotspots,
            'cities' => $mappedHotspots, // backward-compatible alias
            'geojson_url' => asset('geo/kenya-counties.geojson'),
            'returning_share' => round(32 + (($seed % 18)), 1),
            'devices' => (function () use ($seed) {
                $mobile = 58 + ($seed % 8);
                $desktop = 28 + ($seed % 6);
                $tablet = max(4, 100 - $mobile - $desktop);

                return [
                    ['label' => 'Mobile', 'share' => (float) $mobile],
                    ['label' => 'Desktop', 'share' => (float) $desktop],
                    ['label' => 'Tablet', 'share' => (float) $tablet],
                ];
            })(),
            'customers' => max($customers, (int) round($totalSessions * 0.35)),
        ];
    }

    private function periodMetrics(Store $store, Carbon $from, Carbon $to): array
    {
        $orders = Order::query()
            ->where('store_id', $store->id)
            ->where('status', '!=', 'cancelled')
            ->whereBetween('created_at', [$from, $to]);

        $sales = (float) (clone $orders)->sum('total');
        $orderCount = (int) (clone $orders)->count();

        return [
            'sales' => $sales,
            'orders' => $orderCount,
            'average' => $orderCount > 0 ? round($sales / $orderCount, 2) : 0,
            'customers' => (int) Order::query()
                ->where('store_id', $store->id)
                ->whereNotNull('user_id')
                ->whereBetween('created_at', [$from, $to])
                ->distinct('user_id')
                ->count('user_id'),
            'interactions' => (int) DemoSession::query()
                ->where('store_id', $store->id)
                ->whereBetween('created_at', [$from, $to])
                ->count(),
        ];
    }

    private function percentChange(float|int $current, float|int $previous): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    private function orderHeatmap(Store $store, Carbon $from, Carbon $to): array
    {
        $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'];
        $hours = ['09:00', '11:00', '13:00', '15:00', '17:00'];
        $buckets = [];

        foreach ($days as $day) {
            foreach ($hours as $hour) {
                $buckets[$day][$hour] = 0;
            }
        }

        $rows = Order::query()
            ->where('store_id', $store->id)
            ->whereBetween('created_at', [$from, $to])
            ->get(['created_at']);

        foreach ($rows as $order) {
            $day = $order->created_at->format('D');
            $hour = (int) $order->created_at->format('G');

            if (! isset($buckets[$day])) {
                continue;
            }

            $slot = match (true) {
                $hour < 10 => '09:00',
                $hour < 12 => '11:00',
                $hour < 14 => '13:00',
                $hour < 16 => '15:00',
                default => '17:00',
            };

            $buckets[$day][$slot]++;
        }

        $max = max(1, collect($buckets)->flatten()->max());

        return [
            'days' => $days,
            'hours' => $hours,
            'cells' => $buckets,
            'max' => $max,
        ];
    }
}
