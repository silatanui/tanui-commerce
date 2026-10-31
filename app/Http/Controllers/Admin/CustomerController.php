<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $store = admin_store();
        $search = $request->string('q')->toString();

        $customers = User::query()
            ->where('role', 'customer')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($builder) use ($search) {
                    $builder->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->withCount(['orders' => fn ($query) => $query->where('store_id', $store->id)])
            ->orderBy('name')
            ->get();

        return view('admin.customers.index', compact('customers', 'search'));
    }

    public function show(User $customer)
    {
        abort_unless($customer->role === 'customer', 404);
        $store = admin_store();
        $customer->load(['addresses']);

        $orders = $customer->orders()->where('store_id', $store->id)->latest()->get();

        return view('admin.customers.show', compact('customer', 'orders'));
    }

    public function update(Request $request, User $customer)
    {
        abort_unless($customer->role === 'customer', 404);

        $data = $request->validate([
            'status' => ['required', Rule::in(['active', 'suspended'])],
        ]);

        $customer->update(['status' => $data['status']]);

        return back()->with('status', 'Customer account status updated.');
    }
}
