<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Support\Slugs;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        $store = admin_store();
        $search = $request->string('q')->toString();

        $brands = Brand::query()
            ->where('store_id', $store->id)
            ->withCount('products')
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->get();

        return view('admin.brands.index', compact('brands', 'search'));
    }

    public function create()
    {
        return view('admin.brands.form', ['brand' => new Brand]);
    }

    public function store(Request $request)
    {
        $data = $this->validateBrand($request);
        $store = admin_store();

        $brand = Brand::query()->create([
            'store_id' => $store->id,
            'name' => $data['name'],
            'slug' => Slugs::unique(Brand::class, $data['name'], $store->id),
            'description' => $data['description'] ?? null,
            'logo' => $this->logo($request, $store->id),
        ]);

        return redirect()->route('admin.brands.edit', $brand)->with('status', 'Brand created.');
    }

    public function edit(Brand $brand)
    {
        abort_unless((int) $brand->store_id === (int) session('admin_store_id'), 403);

        return view('admin.brands.form', compact('brand'));
    }

    public function update(Request $request, Brand $brand)
    {
        abort_unless((int) $brand->store_id === (int) session('admin_store_id'), 403);
        $data = $this->validateBrand($request);

        $brand->update([
            'name' => $data['name'],
            'slug' => Slugs::unique(Brand::class, $data['name'], $brand->store_id, $brand->id),
            'description' => $data['description'] ?? null,
            'logo' => $this->logo($request, $brand->store_id) ?? $brand->logo,
        ]);

        return redirect()->route('admin.brands.edit', $brand)->with('status', 'Brand updated.');
    }

    public function destroy(Brand $brand)
    {
        abort_unless((int) $brand->store_id === (int) session('admin_store_id'), 403);
        $brand->delete();

        return redirect()->route('admin.brands.index')->with('status', 'Brand deleted.');
    }

    private function validateBrand(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:2048'],
        ]);
    }

    private function logo(Request $request, int $storeId): ?string
    {
        if (! $request->hasFile('logo')) {
            return null;
        }

        return 'storage/'.$request->file('logo')->store('brands/'.$storeId, 'public');
    }
}
