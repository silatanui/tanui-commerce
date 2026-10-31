<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class CompareController extends Controller
{
    public function index()
    {
        $ids = array_slice(session($this->key(), []), 0, 3);
        $products = Product::query()
            ->forStore(current_store())
            ->published()
            ->whereIn('id', $ids)
            ->with(['brand', 'images', 'variants', 'category'])
            ->get();

        return view('storefront.compare', ['products' => $products]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['product_id' => ['required', 'integer']]);
        $product = Product::query()->forStore(current_store())->published()->findOrFail($data['product_id']);
        $ids = session($this->key(), []);

        if (! in_array($product->id, $ids, true)) {
            $ids[] = $product->id;
        }

        session([$this->key() => array_slice($ids, -3)]);

        return back()->with('status', 'Added to comparison. You can compare up to three products.');
    }

    public function destroy(Request $request)
    {
        $data = $request->validate(['product_id' => ['required', 'integer']]);
        $ids = array_values(array_filter(session($this->key(), []), fn ($id) => (int) $id !== (int) $data['product_id']));
        session([$this->key() => $ids]);

        return back()->with('status', 'Removed from comparison.');
    }

    private function key(): string
    {
        return 'compare.'.current_store()->id;
    }
}
