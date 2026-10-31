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

    public function test_administration_opens_without_a_login_screen_for_guests(): void
    {
        $this->catalogue('fashion', 'Admin Catalogue Piece');
        User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $this->get('/admin')->assertOk()->assertSee('Simulated');
        $this->get(route('admin.login'))->assertRedirect(route('admin.dashboard'));

        $customer = User::factory()->create(['role' => 'customer', 'status' => 'active']);
        $this->actingAs($customer)->get('/admin')->assertForbidden();

        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active', 'email' => 'second-admin@example.test']);
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()->assertSee('Simulated');
    }

    public function test_store_assistant_can_complete_demo_purchase_and_track_order(): void
    {
        $catalogue = $this->catalogue('fashion', 'Chat Flow Coat');
        $product = $catalogue['product'];
        $variant = $catalogue['variant'];
        $customer = User::factory()->create(['role' => 'customer', 'status' => 'active']);

        $buy = $this->actingAs($customer)->postJson(route('store.ai.commerce', 'fashion'), [
            'action' => 'buy_now',
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity' => 1,
        ])->assertOk()->assertJsonPath('ui.step', 'checkout');

        $token = $buy->json('session_token');
        $this->assertNotEmpty($token);

        $this->actingAs($customer)->postJson(route('store.ai.commerce', 'fashion'), [
            'action' => 'checkout.save',
            'session_token' => $token,
            'draft' => [
                'full_name' => 'Chat Shopper',
                'email' => 'chat@example.test',
                'phone' => '+254700000099',
                'address_line' => '9 Assistant Way',
                'city' => 'Nairobi',
                'postal_code' => '00100',
                'country' => 'Kenya',
                'shipping_method' => 'pickup',
            ],
        ])->assertOk()->assertJsonPath('ui.step', 'payment');

        $confirmed = $this->actingAs($customer)->postJson(route('store.ai.commerce', 'fashion'), [
            'action' => 'checkout.confirm',
            'session_token' => $token,
        ])->assertOk()->assertJsonPath('ui.step', 'order');

        $orderNumber = $confirmed->json('order.order_number');
        $this->assertNotEmpty($orderNumber);

        $order = Order::query()->where('order_number', $orderNumber)->first();
        $this->assertNotNull($order);
        $this->assertTrue($order->is_demo);
        $this->assertSame('not_processed', $order->payment_status);
        $this->assertSame($customer->id, $order->user_id);

        $this->actingAs($customer)->postJson(route('store.ai.commerce', 'fashion'), [
            'action' => 'order.track',
            'session_token' => $token,
            'order_number' => $orderNumber,
        ])->assertOk()->assertJsonPath('ui.step', 'delivery');

        $this->actingAs($customer)->postJson(route('store.ai.commerce', 'fashion'), [
            'action' => 'checkout.save',
            'session_token' => $token,
            'draft' => [
                'full_name' => 'Bad Card',
                'email' => 'bad@example.test',
                'phone' => '+254700000098',
                'address_line' => '1 Fail Road',
                'city' => 'Nairobi',
                'country' => 'Kenya',
                'shipping_method' => 'pickup',
                'card_number' => '4242424242424242',
            ],
        ])->assertStatus(422);
    }

    public function test_store_assistant_chat_intent_opens_cart_ui(): void
    {
        $this->catalogue('fashion', 'Intent Coat');

        $this->postJson(route('store.ai.chat', 'fashion'), [
            'message' => 'View cart please',
        ])->assertOk()->assertJsonPath('ui.step', 'cart');
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
