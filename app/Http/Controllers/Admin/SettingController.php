<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StoreBanner;
use App\Services\StoreConfigurationService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SettingController extends Controller
{
    public function edit()
    {
        $store = admin_store();

        return view('admin.settings', [
            'store' => $store,
            'banners' => $store->banners()->orderBy('sort_order')->get(),
        ]);
    }

    public function update(Request $request, StoreConfigurationService $configuration)
    {
        $store = admin_store();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'primary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'secondary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'currency' => ['required', Rule::in(array_keys(config('commerce.currencies')))],
            'tagline' => ['nullable', 'string', 'max:180'],
            'email' => ['nullable', 'email', 'max:160'],
            'phone' => ['nullable', 'string', 'max:40'],
            'address' => ['nullable', 'string', 'max:180'],
            'hours' => ['nullable', 'string', 'max:180'],
            'footer_text' => ['nullable', 'string', 'max:500'],
            'low_stock_threshold' => ['required', 'integer', 'min:0', 'max:100'],
            'shipping_standard' => ['required', 'numeric', 'min:0'],
            'shipping_express' => ['required', 'numeric', 'min:0'],
            'shipping_pickup' => ['required', 'numeric', 'min:0'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:2048'],
            'favicon' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif,ico', 'max:1024'],
        ]);

        $store->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'primary_color' => $data['primary_color'],
            'secondary_color' => $data['secondary_color'],
            'currency' => $data['currency'],
            'logo' => $request->hasFile('logo') ? 'storage/'.$request->file('logo')->store('stores/'.$store->id, 'public') : $store->logo,
            'favicon' => $request->hasFile('favicon') ? 'storage/'.$request->file('favicon')->store('stores/'.$store->id, 'public') : $store->favicon,
        ]);

        foreach (['tagline', 'email', 'phone', 'address', 'hours', 'footer_text', 'low_stock_threshold'] as $key) {
            $configuration->set($store, $key, $data[$key] ?? '');
        }

        $configuration->set($store, 'shipping_costs', [
            'standard' => $data['shipping_standard'],
            'express' => $data['shipping_express'],
            'pickup' => $data['shipping_pickup'],
        ]);

        $store->unsetRelation('settings');

        return back()->with('status', 'Store configuration saved.');
    }

    public function storeBanner(Request $request)
    {
        $store = admin_store();
        $data = $request->validate([
            'title' => ['required', 'string', 'max:140'],
            'subtitle' => ['nullable', 'string', 'max:180'],
            'link' => ['nullable', 'string', 'max:180'],
            'position' => ['required', Rule::in(['hero', 'promo'])],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
        ]);

        StoreBanner::query()->create([
            'store_id' => $store->id,
            'title' => $data['title'],
            'subtitle' => $data['subtitle'] ?? null,
            'link' => $data['link'] ?? route('store.shop', $store->slug),
            'position' => $data['position'],
            'sort_order' => (int) $store->banners()->max('sort_order') + 1,
            'status' => 'active',
            'image' => $request->hasFile('image') ? 'storage/'.$request->file('image')->store('banners/'.$store->id, 'public') : null,
        ]);

        return back()->with('status', 'Banner added.');
    }

    public function destroyBanner(StoreBanner $banner)
    {
        abort_unless((int) $banner->store_id === (int) session('admin_store_id'), 403);
        $banner->delete();

        return back()->with('status', 'Banner removed.');
    }
}
