<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ComingSoonController extends Controller
{
    /** @var array<string, array{title: string, summary: string, note: string}> */
    private const FEATURES = [
        'lookbook' => [
            'title' => 'Lookbook',
            'summary' => 'Editorial seasonal stories and outfit boards.',
            'note' => 'This demonstration storefront does not include a lookbook module yet.',
        ],
        'gift-cards' => [
            'title' => 'Gift cards',
            'summary' => 'Digital gift balances for demonstration purchases.',
            'note' => 'Gift cards are not issued here. No payment or balance is stored.',
        ],
        'loyalty' => [
            'title' => 'Loyalty programme',
            'summary' => 'Points, tiers and member-only sample offers.',
            'note' => 'Loyalty rewards are planned for a future demonstration release.',
        ],
        'size-guide' => [
            'title' => 'Size guide',
            'summary' => 'Fit charts and measurement tips for apparel and footwear.',
            'note' => 'Detailed sizing content is not connected to this catalogue yet.',
        ],
        'style-advice' => [
            'title' => 'Style advice',
            'summary' => 'Personal styling prompts for the demonstration brands.',
            'note' => 'Live advice and chat are not available in this portfolio build.',
        ],
        'shipping' => [
            'title' => 'Shipping information',
            'summary' => 'Delivery options, timelines and collection points.',
            'note' => 'Checkout already shows sample shipping methods. A dedicated guide is coming soon.',
        ],
        'returns' => [
            'title' => 'Returns & exchanges',
            'summary' => 'How demonstration returns would work in a live shop.',
            'note' => 'No returns are processed. Orders remain fictional.',
        ],
        'faq' => [
            'title' => 'Help & FAQ',
            'summary' => 'Answers about accounts, carts and demonstration checkout.',
            'note' => 'A full help centre is not part of this release.',
        ],
        'stores' => [
            'title' => 'Find a store',
            'summary' => 'Map and hours for fictional retail locations.',
            'note' => 'Store locator content has not been added to this demo.',
        ],
        'new-arrivals' => [
            'title' => 'New arrivals hub',
            'summary' => 'A curated arrivals landing experience.',
            'note' => 'Browse new items from Shop for now. A dedicated hub is coming soon.',
        ],
        'sale' => [
            'title' => 'Sale edit',
            'summary' => 'A dedicated sale landing with sample markdowns.',
            'note' => 'Sale badges already appear on products. A sale page is coming soon.',
        ],
        'products' => [
            'title' => 'Products directory',
            'summary' => 'An alternate products index for browsing the catalogue.',
            'note' => 'Use Shop to browse the live demonstration catalogue. This alternate directory is coming soon.',
        ],
        'brands' => [
            'title' => 'Shop by brand',
            'summary' => 'A brand directory with logos and short stories.',
            'note' => 'Brand filters are available on Shop. A brand landing page is coming soon.',
        ],
        'appointments' => [
            'title' => 'Book an appointment',
            'summary' => 'Studio visits and virtual styling slots.',
            'note' => 'Appointments are not scheduled in this demonstration.',
        ],
    ];

    public function show(string $store, string $feature): View|Response|\Illuminate\Http\RedirectResponse
    {
        $feature = strtolower($feature);

        if ($feature === 'new-arrivals') {
            return redirect()->route('store.arrivals', $store);
        }

        if ($feature === 'sale') {
            return redirect()->route('store.deals', $store);
        }

        if ($feature === 'products') {
            return redirect()->route('store.bestsellers', $store);
        }

        if ($feature === 'lookbook') {
            return redirect()->route('store.lookbook', $store);
        }

        if ($feature === 'gift-cards') {
            return redirect()->route('store.gift-cards', $store);
        }

        if (! isset(self::FEATURES[$feature])) {
            abort(404);
        }

        $meta = self::FEATURES[$feature];

        return view('storefront.coming-soon', [
            'feature' => $feature,
            'title' => $meta['title'],
            'summary' => $meta['summary'],
            'note' => $meta['note'],
        ]);
    }
}
