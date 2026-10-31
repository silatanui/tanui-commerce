<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\ProductVariant;
use App\Models\Store;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    public function change(Store $store, ProductVariant $variant, int $quantity, string $type, ?string $reference = null, ?string $notes = null): void
    {
        $variant->loadMissing('product');

        if (! $variant->product || $variant->product->store_id !== $store->id) {
            abort(404);
        }

        $next = $variant->stock_quantity + $quantity;

        if ($next < 0) {
            throw ValidationException::withMessages([
                'quantity' => 'Demonstration stock cannot fall below zero.',
            ]);
        }

        $variant->update(['stock_quantity' => $next]);

        InventoryMovement::query()->create([
            'store_id' => $store->id,
            'product_variant_id' => $variant->id,
            'quantity' => $quantity,
            'movement_type' => $type,
            'reference' => $reference,
            'notes' => $notes,
        ]);
    }

    public function adjust(Store $store, ProductVariant $variant, int $quantity, string $type, ?string $reference = null, ?string $notes = null): void
    {
        DB::transaction(function () use ($store, $variant, $quantity, $type, $reference, $notes) {
            $this->change($store, $variant, $quantity, $type, $reference, $notes);
        });
    }
}
