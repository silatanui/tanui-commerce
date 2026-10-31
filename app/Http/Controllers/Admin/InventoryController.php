<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryMovement;
use App\Models\ProductVariant;
use App\Services\InventoryService;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $store = admin_store();
        $threshold = (int) ($store->setting('low_stock_threshold') ?: config('commerce.low_stock_threshold'));
        $search = $request->string('q')->toString();

        $variants = ProductVariant::query()
            ->with('product')
            ->whereHas('product', function ($query) use ($store, $search) {
                $query->where('store_id', $store->id);
                if ($search !== '') {
                    $query->where(function ($builder) use ($search) {
                        $builder->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%");
                    });
                }
            })
            ->orderBy('stock_quantity')
            ->paginate(20)
            ->withQueryString();

        $movements = InventoryMovement::query()
            ->where('store_id', $store->id)
            ->with('variant.product')
            ->latest()
            ->limit(12)
            ->get();

        return view('admin.inventory.index', compact('variants', 'movements', 'threshold', 'search'));
    }

    public function adjust(Request $request, InventoryService $inventory)
    {
        $store = admin_store();
        $data = $request->validate([
            'variant_id' => ['required', 'integer'],
            'quantity' => ['required', 'integer', 'not_in:0'],
            'movement_type' => ['required', 'in:restock,adjustment,damage'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $variant = ProductVariant::query()
            ->whereKey($data['variant_id'])
            ->whereHas('product', fn ($query) => $query->where('store_id', $store->id))
            ->firstOrFail();

        $quantity = (int) $data['quantity'];

        if ($data['movement_type'] === 'damage' && $quantity > 0) {
            $quantity *= -1;
        }

        $inventory->adjust($store, $variant, $quantity, $data['movement_type'], $variant->sku, $data['notes'] ?? null);

        return back()->with('status', 'Inventory movement recorded.');
    }
}
