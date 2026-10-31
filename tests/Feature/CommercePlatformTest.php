<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CommercePlatformTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
    }

    public function test_storefronts_are_isolated_and_marked_as_demonstrations(): void
    {
        $fashion = $this->catalogue('fashion', 'Fashion Only Coat');
        $electronics = $this->catalogue('electronics', 'Electronics Only Radio');

        $this->get(route('store.home', 'fashion'))
            ->assertOk()
            ->assertSee('Tanui Fashion')
            ->assertSee(config('demo.notice'));

        $this->get(route('store.shop', 'fashion'))
            ->assertOk()
            ->assertSee('Fashion Only Coat')
            ->assertDontSee('Electronics Only Radio');

        $this->get(route('store.search', ['store' => 'electronics', 'q' => 'Coat']))
            ->assertOk()
            ->assertDontSee('Fashion Only Coat');

        $this->get(route('store.product', ['store' => 'electronics', 'product' => $fashion['product']->slug]))
            ->assertNotFound();
    }

    public function test_cart_uses_database_prices_and_checkout_is_simulated(): void
    {
        $catalogue = $this->catalogue('fashion', 'Sample Column Dress');
        $product = $catalogue['product'];
        $variant = $catalogue['variant'];

        $added = $this->post(route('store.cart.add', 'fashion'), [
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity' => 2,
            'unit_price' => 1,
        ])->assertRedirect();

        $this->assertDatabaseHas('cart_items', [
            'variant_id' => $variant->id,
            'quantity' => 2,
            'unit_price' => 1000,
        ]);

        $placed = $this->carrySession($added)->post(route('store.checkout.place', 'fashion'), [
                'full_name' => 'Amina Otieno',
                'email' => 'amina@example.test',
                'phone' => '+254700000000',
                'address_line' => '12 Sample Lane',
                'city' => 'Nairobi',
                'postal_code' => '00100',
                'country' => 'Kenya',
                'shipping_method' => 'pickup',
                'accept_demo' => '1',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $order = Order::query()->first();
        $this->assertNotNull($order);
        $this->assertTrue($order->is_demo);
        $this->assertSame('not_processed', $order->payment_status);
        $this->assertEquals(2000, (float) $order->total);
        $this->assertSame(3, $variant->fresh()->stock_quantity);

        $this->carrySession($placed)
            ->get(route('store.order', ['store' => 'fashion', 'order' => $order->order_number]))
            ->assertOk()
            ->assertSee(config('demo.confirmation'));
    }

    public function test_checkout_rejects_card_details(): void
    {
        $catalogue = $this->catalogue('market', 'Sample Coffee');

        $added = $this->post(route('store.cart.add', 'market'), [
            'product_id' => $catalogue['product']->id,
            'variant_id' => $catalogue['variant']->id,
            'quantity' => 1,
        ])->assertRedirect();

        $this->carrySession($added)->from(route('store.checkout', 'market'))
            ->post(route('store.checkout.place', 'market'), [
                'full_name' => 'Guest Shopper',
                'email' => 'guest@example.test',
                'phone' => '+254700000001',
                'address_line' => '1 Demo Road',
                'city' => 'Nairobi',
                'country' => 'Kenya',
                'shipping_method' => 'pickup',
                'accept_demo' => '1',
                'card_number' => '4242424242424242',
                'cvv' => '123',
            ])
            ->assertSessionHasErrors(['card_number', 'cvv']);

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_administration_requires_an_active_administrator(): void
    {
        $this->catalogue('fashion', 'Admin Catalogue Piece');

        $this->get('/admin')->assertRedirect(route('admin.login'));

        $customer = User::factory()->create(['role' => 'customer', 'status' => 'active']);
        $this->actingAs($customer)->get('/admin')->assertForbidden();

        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()->assertSee('Simulated');
    }

    private function carrySession($response): static
    {
        foreach ($response->headers->getCookies() as $cookie) {
            $this->withUnencryptedCookie($cookie->getName(), $cookie->getValue());
        }

        return $this;
    }

    private function catalogue(string $slug, string $productName): array
    {
        $store = Store::query()->create([
            'name' => 'Tanui '.Str::headline($slug),
            'slug' => $slug,
            'description' => 'Demonstration store',
            'primary_color' => '#112233',
            'secondary_color' => '#111111',
            'currency' => 'KES',
            'status' => 'active',
        ]);

        $category = Category::query()->create([
            'store_id' => $store->id,
            'name' => 'General',
            'slug' => 'general',
            'status' => 'active',
            'sort_order' => 0,
        ]);

        $product = Product::query()->create([
            'store_id' => $store->id,
            'category_id' => $category->id,
            'name' => $productName,
            'slug' => Str::slug($productName),
            'sku' => strtoupper($slug).'-1',
            'short_description' => $productName,
            'description' => $productName.' demonstration product',
            'price' => 1000,
            'status' => 'published',
            'product_type' => 'simple',
        ]);

        $variant = ProductVariant::query()->create([
            'product_id' => $product->id,
            'sku' => $product->sku.'-1',
            'attributes' => ['Size' => 'M'],
            'stock_quantity' => 5,
            'status' => 'active',
        ]);

        return compact('store', 'product', 'variant');
    }
}
