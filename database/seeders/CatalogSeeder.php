<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Store;
use App\Support\DemoArtwork;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = require database_path('seeders/data/catalog.php');

        foreach ($catalog as $slug => $data) {
            $store = Store::query()->where('slug', $slug)->firstOrFail();
            $categories = $this->categories($store, $data['categories']);
            $brands = $this->brands($store, $data['brands']);
            $this->products($store, $data['products'], $categories, $brands);
            $this->coupon($store, $data['coupon']);
        }
    }

    private function categories(Store $store, array $categories): array
    {
        $map = [];

        foreach ($categories as $index => $category) {
            $parent = $this->makeCategory($store, $category['name'], null, $index);
            $map[$parent->name] = $parent;

            foreach ($category['children'] ?? [] as $childIndex => $child) {
                $childModel = $this->makeCategory($store, $child, $parent->id, $childIndex);
                $map[$childModel->name] = $childModel;
            }
        }

        return $map;
    }

    private function makeCategory(Store $store, string $name, ?int $parentId, int $sort): Category
    {
        return Category::query()->create([
            'store_id' => $store->id,
            'parent_id' => $parentId,
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $name.' in the '.$store->name.' demonstration catalogue.',
            'status' => 'active',
            'sort_order' => $sort,
            'image' => DemoArtwork::resolve(
                'demo-assets/categories/'.$store->slug.'-'.Str::slug($name),
                $name,
                strtoupper($store->slug),
                $store->slug
            ),
        ]);
    }

    private function brands(Store $store, array $brands): array
    {
        $map = [];

        foreach ($brands as $brand) {
            $model = Brand::query()->create([
                'store_id' => $store->id,
                'name' => $brand['name'],
                'slug' => Str::slug($brand['name']),
                'description' => $brand['description'],
                'logo' => DemoArtwork::resolve(
                    'demo-assets/brands/'.$store->slug.'-'.Str::slug($brand['name']),
                    $brand['name'],
                    'BRAND',
                    $store->slug
                ),
            ]);
            $map[$model->name] = $model;
        }

        return $map;
    }

    private function products(Store $store, array $products, array $categories, array $brands): void
    {
        foreach ($products as $index => $product) {
            $category = $categories[$product['category']] ?? null;
            $brand = $brands[$product['brand']] ?? null;

            if (! $category || ! $brand) {
                throw new \RuntimeException('Missing category or brand for '.$product['name']);
            }

            $record = Product::query()->create([
                'store_id' => $store->id,
                'category_id' => $category->id,
                'brand_id' => $brand->id,
                'name' => $product['name'],
                'slug' => Str::slug($product['name']),
                'sku' => strtoupper(substr($store->slug, 0, 3)).'-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                'description' => $product['description'],
                'short_description' => $product['short'],
                'price' => $product['price'],
                'compare_at_price' => $product['compare'] ?? null,
                'product_type' => count($product['variants']) > 1 ? 'variable' : 'simple',
                'specifications' => $product['specs'],
                'status' => 'published',
                'is_featured' => (bool) ($product['featured'] ?? false),
                'created_at' => ! empty($product['new']) ? now()->subDays(4) : now()->subDays(70),
                'updated_at' => now(),
            ]);

            $slug = Str::slug($product['name']);

            foreach ([0, 1] as $imageIndex) {
                $record->images()->create([
                    'image_path' => DemoArtwork::resolve(
                        'demo-assets/products/'.$store->slug.'-'.$slug.($imageIndex ? '-alt' : ''),
                        $product['name'].($imageIndex ? ' detail' : ''),
                        strtoupper($store->slug),
                        $store->slug
                    ),
                    'alt_text' => $product['name'].' sample image',
                    'is_primary' => $imageIndex === 0,
                    'position' => $imageIndex,
                ]);
            }

            foreach ($product['variants'] as $variantIndex => $variant) {
                $stock = (int) ($variant['stock'] ?? 0);
                $price = $variant['price'] ?? null;
                unset($variant['stock'], $variant['price']);

                $variantModel = $record->variants()->create([
                    'sku' => $record->sku.'-'.($variantIndex + 1),
                    'attributes' => $variant,
                    'price' => $price,
                    'stock_quantity' => $stock,
                    'status' => 'active',
                ]);

                InventoryMovement::query()->create([
                    'store_id' => $store->id,
                    'product_variant_id' => $variantModel->id,
                    'quantity' => $stock,
                    'movement_type' => 'restock',
                    'reference' => $record->sku,
                    'notes' => 'Opening demonstration stock',
                ]);
            }
        }
    }

    private function coupon(Store $store, array $coupon): void
    {
        Coupon::query()->create([
            'store_id' => $store->id,
            'code' => $coupon['code'],
            'discount_type' => $coupon['discount_type'],
            'discount_value' => $coupon['discount_value'],
            'minimum_amount' => $coupon['minimum_amount'],
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addYear(),
            'status' => 'active',
        ]);
    }
}
