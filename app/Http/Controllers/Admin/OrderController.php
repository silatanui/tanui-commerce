<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Order::class);
        $store = admin_store();
        $search = $request->string('q')->toString();
        $status = $request->string('status')->toString();

        $orders = Order::query()
            ->where('store_id', $store->id)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($builder) use ($search) {
                    $builder->where('order_number', 'like', "%{$search}%")
                        ->orWhere('customer_details->full_name', 'like', "%{$search}%")
                        ->orWhere('customer_details->email', 'like', "%{$search}%");
                });
            })
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->latest()
            ->get();

        return view('admin.orders.index', [
            'orders' => $orders,
            'search' => $search,
            'status' => $status,
            'statuses' => config('commerce.order_statuses'),
        ]);
    }

    public function show(Order $order)
    {
        $this->authorize('view', $order);
        $order->load(['items', 'shipment', 'user']);

        return view('admin.orders.show', [
            'order' => $order,
            'statuses' => config('commerce.order_statuses'),
        ]);
    }

    public function update(Request $request, Order $order, InventoryService $inventory)
    {
        $this->authorize('update', $order);

        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(config('commerce.order_statuses')))],
        ]);

        $previous = $order->status;
        $order->update(['status' => $data['status']]);

        if ($order->shipment) {
            $shipmentStatus = match ($data['status']) {
                'shipped' => 'shipped',
                'delivered' => 'delivered',
                'cancelled' => 'cancelled',
                default => $order->shipment->status,
            };
            $order->shipment->update(['status' => $shipmentStatus]);
        }

        if ($previous !== 'cancelled' && $data['status'] === 'cancelled') {
            foreach ($order->items as $item) {
                if ($item->variant && $item->variant->product && $item->variant->product->store_id === $order->store_id) {
                    $inventory->adjust($order->store, $item->variant, $item->quantity, 'adjustment', $order->order_number, 'Restocked after cancellation');
                }
            }
        }

        return back()->with('status', 'Demonstration order status updated. No payment was collected.');
    }
}
