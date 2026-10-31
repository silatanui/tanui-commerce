<?php

namespace App\Http\Requests\Storefront;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) config('demo.allow_checkout');
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160'],
            'phone' => ['required', 'string', 'max:40'],
            'address_line' => ['required', 'string', 'max:180'],
            'city' => ['required', 'string', 'max:80'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => ['required', 'string', 'max:80'],
            'shipping_method' => ['required', Rule::in(array_keys(config('commerce.shipping_methods', [])))],
            'accept_demo' => ['accepted'],
            'card_number' => ['prohibited'],
            'card' => ['prohibited'],
            'cvv' => ['prohibited'],
            'cvc' => ['prohibited'],
            'pan' => ['prohibited'],
            'payment_token' => ['prohibited'],
            'payment_method' => ['prohibited'],
            'role' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'accept_demo.accepted' => 'Confirm that you understand this is a demonstration order.',
            'card_number.prohibited' => 'Payment cards are not accepted.',
            'cvv.prohibited' => 'Payment cards are not accepted.',
            'cvc.prohibited' => 'Payment cards are not accepted.',
        ];
    }
}
