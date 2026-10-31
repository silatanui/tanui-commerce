<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user || ! $user->isAdmin()) {
            return false;
        }

        $product = $this->route('product');

        if ($product instanceof Product) {
            return $user->can('update', $product);
        }

        return $user->can('create', Product::class);
    }

    public function rules(): array
    {
        $storeId = (int) session('admin_store_id');
        $productId = $this->route('product')?->id;

        return [
            'name' => ['required', 'string', 'max:180'],
            'category_id' => ['required', Rule::exists('categories', 'id')->where('store_id', $storeId)],
            'brand_id' => ['nullable', Rule::exists('brands', 'id')->where('store_id', $storeId)],
            'sku' => [
                'nullable',
                'string',
                'max:60',
                Rule::unique('products', 'sku')->where('store_id', $storeId)->ignore($productId),
            ],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:5000'],
            'price' => ['required', 'numeric', 'min:0'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['published', 'draft'])],
            'is_featured' => ['nullable', 'boolean'],
            'spec_names' => ['nullable', 'array'],
            'spec_names.*' => ['nullable', 'string', 'max:80'],
            'spec_values' => ['nullable', 'array'],
            'spec_values.*' => ['nullable', 'string', 'max:180'],
            'variants' => ['nullable', 'array'],
            'variants.*.id' => ['nullable', 'integer'],
            'variants.*.sku' => ['nullable', 'string', 'max:60'],
            'variants.*.price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.stock' => ['nullable', 'integer', 'min:0'],
            'variants.*.attributes' => ['nullable', 'array'],
            'variants.*.attributes.*.name' => ['nullable', 'string', 'max:40'],
            'variants.*.attributes.*.value' => ['nullable', 'string', 'max:40'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
        ];
    }
}
