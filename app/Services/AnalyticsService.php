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

        return [
            'from' => $from,
            'to' => $to,
            'sales' => $current['sales'],
            'orders' => $current['orders'],
            'average' => $current['average'],
            'products' => Product::query()->where('store_id', $store->id)->where('status', 'published')->count(),
            'customers' => $current['customers'],
            'interactions' => $current['interactions'],
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
