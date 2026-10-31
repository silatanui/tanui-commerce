<?php

namespace Database\Seeders;

use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Seeder;

class CommerceSeeder extends Seeder
{
    public function run(): void
    {
        $amina = User::query()->where('email', 'amina@example.test')->firstOrFail();
        $david = User::query()->where('email', 'david@example.test')->firstOrFail();
        $statuses = ['confirmed', 'processing', 'shipped', 'delivered', 'pending'];

        foreach (Store::query()->orderBy('id')->get() as $offset => $store) {
            $products = Product::query()->forStore($store)->with('variants')->get();

            if ($products->count() < 2) {
                continue;
            }

            $comments = [
                [5, 'A clear product page and the sample variations were easy to follow.'],
                [4, 'Useful demonstration of specifications, price and stock. Nothing here is a real purchase.'],
                [5, 'Strong sample photography and sizing notes for a portfolio walkthrough.'],
                [3, 'Fine for demonstration purposes — remember these prices are fictional.'],
                [4, 'Cart and checkout flows felt complete even without a live payment gateway.'],
                [5, 'Nice catalogue detail; the rating helps the storefront feel production-ready.'],
            ];

            foreach ($products as $index => $product) {
                $aminaReview = $comments[$index % count($comments)];
                $davidReview = $comments[($index + 2) % count($comments)];
                $this->review($product, $amina, $aminaReview[0], $aminaReview[1]);
                $this->review($product, $david, max(3, $davidReview[0] - 1), $davidReview[1]);
            }

            foreach ([[$amina, 2, 1], [$david, 6, 1], [null, 10, 2]] as $index => [$customer, $days, $quantity]) {
                $product = $products[$index % $products->count()];
                $variant = $product->variants->first();

                if (! $variant || $variant->stock_quantity < $quantity) {
                    continue;
                }

                $unit = (float) ($variant->price ?? $product->price);
                $subtotal = round($unit * $quantity, 2);
                $shipping = 350;
                $status = $statuses[($offset + $index) % count($statuses)];

                $order = Order::query()->create([
                    'store_id' => $store->id,
                    'user_id' => $customer?->id,
                    'order_number' => 'TC-'.strtoupper(substr($store->slug, 0, 3)).'-'.now()->format('y').'-'.(41000 + ($store->id * 10) + $index),
                    'subtotal' => $subtotal,
                    'discount' => 0,
                    'shipping_cost' => $shipping,
                    'total' => $subtotal + $shipping,
                    'currency' => $store->currency,
                    'status' => $status,
                    'payment_status' => 'not_processed',
                    'is_demo' => true,
                    'customer_details' => [
                        'full_name' => $customer?->name ?? 'Guest Shopper',
                        'email' => $customer?->email ?? 'guest@example.test',
                        'phone' => $customer?->phone ?? '+254 700 000 000',
                    ],
                    'shipping_details' => [
                        'address_line' => 'Sample address for '.$store->name,
                        'city' => 'Nairobi',
                        'postal_code' => '00100',
                        'country' => 'Kenya',
                        'method' => 'standard',
                        'method_label' => 'Standard delivery',
                    ],
                    'created_at' => now()->subDays($days),
                    'updated_at' => now()->subDays(max(0, $days - 1)),
                ]);

                $order->items()->create([
                    'product_id' => $product->id,
                    'variant_id' => $variant->id,
                    'product_name' => $product->name,
                    'sku' => $variant->sku,
                    'quantity' => $quantity,
                    'unit_price' => $unit,
                    'subtotal' => $subtotal,
                    'attributes' => $variant->getAttribute('attributes') ?? [],
                ]);

                $order->shipment()->create([
                    'shipping_method' => 'Standard delivery',
                    'shipping_cost' => $shipping,
                    'status' => in_array($status, ['shipped', 'delivered'], true) ? $status : 'pending',
                    'tracking_reference' => 'DEMO-'.$order->order_number,
                ]);

                $variant->decrement('stock_quantity', $quantity);

                InventoryMovement::query()->create([
                    'store_id' => $store->id,
                    'product_variant_id' => $variant->id,
                    'quantity' => -1 * $quantity,
                    'movement_type' => 'sale',
                    'reference' => $order->order_number,
                    'notes' => 'Seeded demonstration order',
                    'created_at' => $order->created_at,
                    'updated_at' => $order->created_at,
                ]);
            }
        }
    }

    private function review(Product $product, User $user, int $rating, string $text): void
    {
        ProductReview::query()->create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'rating' => $rating,
            'review' => $text,
            'status' => 'approved',
        ]);
    }
}
