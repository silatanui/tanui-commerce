<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function show(string $store, string $order): View
    {
        $storeModel = current_store();
        $record = Order::query()
            ->where('store_id', $storeModel->id)
            ->where('order_number', $order)
            ->with(['items', 'shipment'])
            ->firstOrFail();

        $owns = auth()->check() && (int) $record->user_id === (int) auth()->id();
        $known = in_array($record->order_number, session('demo_order_numbers', []), true);
        $admin = auth()->user()?->isAdmin();

        abort_unless($owns || $known || $admin, 403);

        return view('storefront.order', ['order' => $record]);
    }
}
