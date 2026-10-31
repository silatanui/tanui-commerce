<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    public function index()
    {
        $store = current_store();

        return view('storefront.account.index', [
            'orders' => Order::query()->where('store_id', $store->id)->where('user_id', auth()->id())->latest()->limit(5)->get(),
        ]);
    }

    public function profile()
    {
        return view('storefront.account.profile');
    }

    public function updateProfile(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['required', 'email', 'max:160', Rule::unique('users', 'email')->ignore($request->user()->id)],
            'password' => ['nullable', 'confirmed', 'min:8'],
        ]);

        $request->user()->fill([
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'],
        ]);

        if (! empty($data['password'])) {
            $request->user()->password = $data['password'];
        }

        $request->user()->save();

        return back()->with('status', 'Demonstration profile updated.');
    }

    public function orders()
    {
        $orders = Order::query()
            ->where('store_id', current_store()->id)
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('storefront.account.orders', ['orders' => $orders]);
    }

    public function addresses()
    {
        return view('storefront.account.addresses', [
            'addresses' => CustomerAddress::query()->where('user_id', auth()->id())->latest()->get(),
        ]);
    }

    public function storeAddress(Request $request)
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:40'],
            'address_line' => ['required', 'string', 'max:180'],
            'city' => ['required', 'string', 'max:80'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => ['required', 'string', 'max:80'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        if ($request->boolean('is_default')) {
            CustomerAddress::query()->where('user_id', $request->user()->id)->update(['is_default' => false]);
        }

        CustomerAddress::query()->create($data + [
            'user_id' => $request->user()->id,
            'is_default' => $request->boolean('is_default'),
        ]);

        return back()->with('status', 'Address saved for this demonstration account.');
    }

    public function destroyAddress(CustomerAddress $address)
    {
        abort_unless((int) $address->user_id === (int) auth()->id(), 403);
        $address->delete();

        return back()->with('status', 'Address removed.');
    }
}
