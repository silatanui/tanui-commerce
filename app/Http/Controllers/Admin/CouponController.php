<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CouponController extends Controller
{
    public function index()
    {
        $coupons = Coupon::query()->where('store_id', admin_store()->id)->latest()->paginate(15);

        return view('admin.coupons.index', compact('coupons'));
    }

    public function create()
    {
        return view('admin.coupons.form', ['coupon' => new Coupon(['status' => 'active', 'discount_type' => 'percentage'])]);
    }

    public function store(Request $request)
    {
        $coupon = Coupon::query()->create($this->payload($request));

        return redirect()->route('admin.coupons.edit', $coupon)->with('status', 'Demonstration coupon created.');
    }

    public function edit(Coupon $coupon)
    {
        abort_unless((int) $coupon->store_id === (int) session('admin_store_id'), 403);

        return view('admin.coupons.form', compact('coupon'));
    }

    public function update(Request $request, Coupon $coupon)
    {
        abort_unless((int) $coupon->store_id === (int) session('admin_store_id'), 403);
        $coupon->update($this->payload($request, $coupon));

        return redirect()->route('admin.coupons.edit', $coupon)->with('status', 'Coupon updated.');
    }

    public function destroy(Coupon $coupon)
    {
        abort_unless((int) $coupon->store_id === (int) session('admin_store_id'), 403);
        $coupon->delete();

        return redirect()->route('admin.coupons.index')->with('status', 'Coupon deleted.');
    }

    private function payload(Request $request, ?Coupon $coupon = null): array
    {
        $storeId = admin_store()->id;
        $data = $request->validate([
            'code' => [
                'required',
                'string',
                'max:40',
                Rule::unique('coupons', 'code')->where('store_id', $storeId)->ignore($coupon?->id),
            ],
            'discount_type' => ['required', Rule::in(['percentage', 'fixed'])],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'minimum_amount' => ['nullable', 'numeric', 'min:0'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        return [
            'store_id' => $storeId,
            'code' => strtoupper($data['code']),
            'discount_type' => $data['discount_type'],
            'discount_value' => $data['discount_value'],
            'minimum_amount' => $data['minimum_amount'] ?? 0,
            'starts_at' => $data['starts_at'] ?? null,
            'expires_at' => $data['expires_at'] ?? null,
            'status' => $data['status'],
        ];
    }
}
