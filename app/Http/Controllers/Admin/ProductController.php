<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\InventoryService;
use App\Support\Slugs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Product::class);
        $store = admin_store();
        $search = $request->string('q')->toString();

        $products = Product::query()
            ->forStore($store)
            ->with(['category', 'brand', 'images', 'variants'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($builder) use ($search) {
                    $builder->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->get();

        return view('admin.products.index', compact('products', 'search'));
    }

    public function create()
    {
        $this->authorize('create', Product::class);

        return view('admin.products.form', $this->formData(new Product(['status' => 'published'])));
    }

    public function store(ProductRequest $request)
    {
        $store = admin_store();

        $product = DB::transaction(function () use ($request, $store) {
            $product = Product::query()->create($this->payload($request, $store->id));
            $this->syncVariants($product, $request->input('variants', []), $store);
            $this->storeImages($product, $request);

            return $product;
        });

        return redirect()->route('admin.products.edit', $product)->with('status', 'Product created.');
    }

    public function edit(Product $product)
    {
        $this->authorize('update', $product);
        $product->load(['variants', 'images']);

        return view('admin.products.form', $this->formData($product));
    }

    public function update(ProductRequest $request, Product $product)
    {
        $this->authorize('update', $product);
        $store = admin_store();

        DB::transaction(function () use ($request, $product, $store) {
            $product->update($this->payload($request, $store->id, $product));
            $this->syncVariants($product, $request->input('variants', []), $store);
            $this->storeImages($product, $request);
        });

        return redirect()->route('admin.products.edit', $product)->with('status', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);
        $product->delete();

        return redirect()->route('admin.products.index')->with('status', 'Product moved to the archive.');
    }

    private function formData(Product $product): array
    {
        $store = admin_store();

        return [
            'product' => $product,
            'categories' => Category::query()->where('store_id', $store->id)->orderBy('name')->get(),
            'brands' => Brand::query()->where('store_id', $store->id)->orderBy('name')->get(),
        ];
    }

    private function payload(ProductRequest $request, int $storeId, ?Product $existing = null): array
    {
        $specs = [];

        foreach ($request->input('spec_names', []) as $index => $name) {
            $value = $request->input('spec_values.'.$index);
            if (filled($name) && filled($value)) {
                $specs[$name] = $value;
            }
        }

        $name = $request->string('name')->toString();

        return [
            'store_id' => $storeId,
            'category_id' => $request->integer('category_id'),
            'brand_id' => $request->input('brand_id') ?: null,
            'name' => $name,
            'slug' => Slugs::unique(Product::class, $name, $storeId, $existing?->id),
            'sku' => $request->filled('sku')
                ? $request->string('sku')->toString()
                : ($existing->sku ?? Slugs::sku(Product::class, admin_store()->slug, $storeId, $existing?->id)),
            'short_description' => $request->input('short_description'),
            'description' => $request->input('description'),
            'price' => $request->input('price'),
            'compare_at_price' => $request->input('compare_at_price') ?: null,
            'product_type' => count(array_filter($request->input('variants', []))) > 1 ? 'variable' : 'simple',
            'specifications' => $specs,
            'status' => $request->input('status'),
            'is_featured' => $request->boolean('is_featured'),
        ];
    }

    private function syncVariants(Product $product, array $rows, $store): void
    {
        $kept = [];
        $inventory = app(InventoryService::class);

        foreach (array_values($rows) as $index => $row) {
            $attributes = [];

            foreach ($row['attributes'] ?? [] as $attribute) {
                $name = trim((string) ($attribute['name'] ?? ''));
                $value = trim((string) ($attribute['value'] ?? ''));

                if ($name !== '' && $value !== '') {
                    $attributes[$name] = $value;
                }
            }

            $stock = (int) ($row['stock'] ?? 0);
            $hasContent = $attributes !== [] || filled($row['sku'] ?? null) || $stock > 0 || filled($row['price'] ?? null);

            if (! $hasContent && empty($row['id'])) {
                continue;
            }

            $data = [
                'sku' => filled($row['sku'] ?? null) ? $row['sku'] : $product->sku.'-'.($index + 1),
                'attributes' => $attributes,
                'price' => filled($row['price'] ?? null) ? $row['price'] : null,
                'status' => 'active',
            ];

            $variant = null;

            if (! empty($row['id'])) {
                $variant = $product->variants()->whereKey($row['id'])->first();
            }

            if ($variant) {
                $delta = $stock - $variant->stock_quantity;
                $variant->update($data);

                if ($delta !== 0) {
                    $inventory->change($store, $variant->fresh(), $delta, 'adjustment', $product->sku, 'Stock edited with the product');
                }
            } else {
                $variant = $product->variants()->create($data + ['stock_quantity' => 0]);

                if ($stock > 0) {
                    $inventory->change($store, $variant, $stock, 'restock', $product->sku, 'Opening demonstration stock');
                }
            }

            $kept[] = $variant->id;
        }

        if ($kept === []) {
            $variant = $product->variants()->create([
                'sku' => $product->sku.'-1',
                'attributes' => [],
                'price' => null,
                'stock_quantity' => 0,
                'status' => 'active',
            ]);
            $kept[] = $variant->id;
        }

        $product->variants()->whereNotIn('id', $kept)->delete();
        $product->update([
            'product_type' => $product->variants()->count() > 1 ? 'variable' : 'simple',
        ]);
    }

    private function storeImages(Product $product, ProductRequest $request): void
    {
        if (! $request->hasFile('images')) {
            return;
        }

        $position = (int) $product->images()->max('position');
        $hasPrimary = $product->images()->where('is_primary', true)->exists();

        foreach ($request->file('images') as $file) {
            $position++;
            $path = $file->store('products/'.$product->store_id, 'public');

            ProductImage::query()->create([
                'product_id' => $product->id,
                'image_path' => $path,
                'alt_text' => $product->name,
                'is_primary' => ! $hasPrimary,
                'position' => $position,
            ]);

            $hasPrimary = true;
        }
    }
}
