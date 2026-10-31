<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function index()
    {
        return view('admin.stores', [
            'stores' => Store::query()->withCount(['products', 'orders'])->orderBy('name')->get(),
        ]);
    }

    public function switch(Request $request)
    {
        $data = $request->validate([
            'store_id' => ['required', 'integer', 'exists:stores,id'],
        ]);

        $request->session()->put('admin_store_id', (int) $data['store_id']);

        return back()->with('status', 'Active demonstration store updated.');
    }
}
