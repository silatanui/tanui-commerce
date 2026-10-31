<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContentPageController extends Controller
{
    public function faq(): View
    {
        $store = current_store();

        return view('storefront.pages.faq', [
            'store' => $store,
            'items' => [
                [
                    'q' => 'Is this a real shop?',
                    'a' => $store->name.' is a demonstration storefront inside Tanui Commerce. Products, prices and orders are fictional — nothing is charged or shipped.',
                ],
                [
                    'q' => 'How does checkout work?',
                    'a' => 'You can add items, apply sample coupons, choose a demonstration shipping method and place an order. Card details are rejected on purpose so no payment is taken.',
                ],
                [
                    'q' => 'What is the Store assistant?',
                    'a' => 'When enabled, the Store assistant answers from this catalogue using retrieval-augmented generation. It can suggest products, summarise your cart and walk through a demo checkout in chat.',
                ],
                [
                    'q' => 'Can I create an account?',
                    'a' => 'Yes. Customer accounts are local to this demonstration. No verification email is sent and credentials are for portfolio testing only.',
                ],
                [
                    'q' => 'Where do orders go?',
                    'a' => 'Orders appear in your account and in the store admin dashboard so you can inspect inventory, fulfilment status and analytics — still with sample data.',
                ],
                [
                    'q' => 'Who built this?',
                    'a' => 'Tanui Commerce is a multi-store Laravel portfolio by Silat Tanui. Use the platform contact form if you want a similar build for a live business.',
                ],
            ],
        ]);
    }

    public function shipping(): View
    {
        $store = current_store();
        $methods = app(\App\Services\StoreConfigurationService::class)->shippingMethods($store);

        return view('storefront.pages.shipping', [
            'store' => $store,
            'methods' => $methods,
        ]);
    }

    public function returns(): View
    {
        return view('storefront.pages.returns', [
            'store' => current_store(),
        ]);
    }

    public function giftCards(): View
    {
        $store = current_store();

        $denominations = match ($store->slug) {
            'electronics' => [5000, 10000, 25000, 50000],
            'home' => [3000, 7500, 15000, 30000],
            'market' => [1000, 2500, 5000, 10000],
            default => [2000, 5000, 10000, 20000],
        };

        return view('storefront.pages.gift-cards', [
            'store' => $store,
            'denominations' => $denominations,
            'occasions' => [
                'Birthday',
                'Thank you',
                'Congratulations',
                'Just because',
            ],
        ]);
    }

    public function giftCardsPreview(Request $request): RedirectResponse
    {
        $store = current_store();

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:500', 'max:100000'],
            'occasion' => ['required', 'string', 'max:40'],
            'recipient_name' => ['required', 'string', 'max:80'],
            'recipient_email' => ['required', 'email', 'max:120'],
            'message' => ['nullable', 'string', 'max:280'],
        ]);

        return back()
            ->withInput()
            ->with('status', 'Gift card preview for '.money($data['amount'], $store->currency).' to '.$data['recipient_name'].' — demonstration only. No balance was issued and no email was sent.');
    }
}
