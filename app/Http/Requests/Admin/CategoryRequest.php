<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    public function rules(): array
    {
        $storeId = (int) session('admin_store_id');

        return [
            'name' => ['required', 'string', 'max:120'],
            'parent_id' => ['nullable', Rule::exists('categories', 'id')->where('store_id', $storeId)],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::in(['active', 'hidden'])],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
        ];
    }
}
