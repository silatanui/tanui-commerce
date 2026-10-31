<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Category;
use App\Support\Slugs;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $store = admin_store();
        $search = $request->string('q')->toString();

        $categories = Category::query()
            ->where('store_id', $store->id)
            ->with('parent')
            ->withCount('products')
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.categories.index', compact('categories', 'search'));
    }

    public function create()
    {
        return view('admin.categories.form', [
            'category' => new Category(['status' => 'active', 'sort_order' => 0]),
            'parents' => $this->parents(),
        ]);
    }

    public function store(CategoryRequest $request)
    {
        $store = admin_store();
        $name = $request->string('name')->toString();

        $category = Category::query()->create([
            'store_id' => $store->id,
            'parent_id' => $request->input('parent_id') ?: null,
            'name' => $name,
            'slug' => Slugs::unique(Category::class, $name, $store->id),
            'description' => $request->input('description'),
            'status' => $request->input('status'),
            'sort_order' => $request->integer('sort_order'),
            'image' => $this->image($request, $store->id),
        ]);

        return redirect()->route('admin.categories.edit', $category)->with('status', 'Category created.');
    }

    public function edit(Category $category)
    {
        abort_unless((int) $category->store_id === (int) session('admin_store_id'), 403);

        return view('admin.categories.form', [
            'category' => $category,
            'parents' => $this->parents($category->id),
        ]);
    }

    public function update(CategoryRequest $request, Category $category)
    {
        abort_unless((int) $category->store_id === (int) session('admin_store_id'), 403);
        $name = $request->string('name')->toString();
        $parentId = $request->input('parent_id') ?: null;

        if ((int) $parentId === (int) $category->id) {
            return back()->withErrors(['parent_id' => 'A category cannot be its own parent.'])->withInput();
        }

        $category->update([
            'parent_id' => $parentId,
            'name' => $name,
            'slug' => Slugs::unique(Category::class, $name, $category->store_id, $category->id),
            'description' => $request->input('description'),
            'status' => $request->input('status'),
            'sort_order' => $request->integer('sort_order'),
            'image' => $this->image($request, $category->store_id) ?? $category->image,
        ]);

        return redirect()->route('admin.categories.edit', $category)->with('status', 'Category updated.');
    }

    public function destroy(Category $category)
    {
        abort_unless((int) $category->store_id === (int) session('admin_store_id'), 403);

        if ($category->products()->exists() || $category->children()->exists()) {
            return back()->withErrors(['category' => 'Move or remove child categories and products before deleting this category.']);
        }

        $category->delete();

        return redirect()->route('admin.categories.index')->with('status', 'Category deleted.');
    }

    private function parents(?int $ignoreId = null)
    {
        return Category::query()
            ->where('store_id', admin_store()->id)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->orderBy('name')
            ->get();
    }

    private function image(CategoryRequest $request, int $storeId): ?string
    {
        if (! $request->hasFile('image')) {
            return null;
        }

        return 'storage/'.$request->file('image')->store('categories/'.$storeId, 'public');
    }
}
