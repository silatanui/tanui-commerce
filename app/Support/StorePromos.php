<?php

namespace App\Support;

use App\Models\Store;

class StorePromos
{
    /**
     * @return array{wide: list<array<string, string>>, stack: list<array<string, string>>}
     */
    public static function forStore(Store $store): array
    {
        $asset = fn (string $file) => asset('demo-assets/products/'.$file);

        return match ($store->slug) {
            'electronics' => [
                'wide' => [
                    [
                        'eyebrow' => 'Device week',
                        'title' => 'Up to 15% Off',
                        'text' => 'Sample markdowns on featured audio',
                        'cta' => 'Shop Now',
                        'href' => route('store.coming-soon', [$store->slug, 'sale']),
                        'image' => $asset('electronics-studio-headphones.png'),
                        'tone' => 'sand',
                    ],
                    [
                        'eyebrow' => 'New Arrivals',
                        'title' => 'Check out the latest kit',
                        'text' => 'Phones, laptops and accessories',
                        'cta' => 'Shop Now',
                        'href' => route('store.coming-soon', [$store->slug, 'new-arrivals']),
                        'image' => $asset('electronics-nimbus-phone-x.png'),
                        'tone' => 'sky',
                    ],
                    [
                        'eyebrow' => 'Big Deals',
                        'title' => 'Up to 20% Off',
                        'text' => 'Limited demonstration offer',
                        'cta' => 'Shop Now',
                        'href' => route('store.coming-soon', [$store->slug, 'sale']),
                        'image' => $asset('electronics-fieldnote-laptop-14.png'),
                        'tone' => 'mist',
                    ],
                ],
                'stack' => [
                    [
                        'title' => 'Phones',
                        'text' => 'Min. 10% Off',
                        'cta' => 'Shop Now',
                        'href' => route('store.category', [$store->slug, 'phones']),
                        'image' => $asset('electronics-nimbus-phone-x.png'),
                    ],
                    [
                        'title' => 'Watches',
                        'text' => 'Min. 15% Off',
                        'cta' => 'Shop Now',
                        'href' => route('store.shop', $store->slug),
                        'image' => $asset('electronics-smart-watch-s.png'),
                    ],
                    [
                        'title' => 'Audio',
                        'text' => 'Min. 20% Off',
                        'cta' => 'Shop Now',
                        'href' => route('store.category', [$store->slug, 'audio']),
                        'image' => $asset('electronics-aria-buds-pro.png'),
                    ],
                ],
            ],
            'home' => [
                'wide' => [
                    [
                        'eyebrow' => 'Living edit',
                        'title' => 'Up to 25% Off',
                        'text' => 'On selected seating samples',
                        'cta' => 'Shop Now',
                        'href' => route('store.coming-soon', [$store->slug, 'sale']),
                        'image' => $asset('home-three-seat-linen-sofa.png'),
                        'tone' => 'sand',
                    ],
                    [
                        'eyebrow' => 'New Arrivals',
                        'title' => 'Quiet pieces for quieter rooms',
                        'text' => 'Furniture and accessories',
                        'cta' => 'Shop Now',
                        'href' => route('store.coming-soon', [$store->slug, 'new-arrivals']),
                        'image' => $asset('home-lounge-chair.png'),
                        'tone' => 'sky',
                    ],
                    [
                        'eyebrow' => 'Big Deals',
                        'title' => 'Up to 30% Off',
                        'text' => 'Limited demonstration offer',
                        'cta' => 'Shop Now',
                        'href' => route('store.coming-soon', [$store->slug, 'sale']),
                        'image' => $asset('home-ceramic-table-lamp.png'),
                        'tone' => 'mist',
                    ],
                ],
                'stack' => [
                    [
                        'title' => 'Seating',
                        'text' => 'Min. 20% Off',
                        'cta' => 'Shop Now',
                        'href' => route('store.category', [$store->slug, 'living-room']),
                        'image' => $asset('home-lounge-chair.png'),
                    ],
                    [
                        'title' => 'Dining',
                        'text' => 'Min. 15% Off',
                        'cta' => 'Shop Now',
                        'href' => route('store.category', [$store->slug, 'dining']),
                        'image' => $asset('home-oak-dining-table.png'),
                    ],
                    [
                        'title' => 'Lighting',
                        'text' => 'Min. 25% Off',
                        'cta' => 'Shop Now',
                        'href' => route('store.category', [$store->slug, 'accessories']),
                        'image' => $asset('home-ceramic-table-lamp.png'),
                    ],
                ],
            ],
            'market' => [
                'wide' => [
                    [
                        'eyebrow' => 'Pantry sale',
                        'title' => 'Up to 15% Off',
                        'text' => 'On selected grocery samples',
                        'cta' => 'Shop Now',
                        'href' => route('store.coming-soon', [$store->slug, 'sale']),
                        'image' => $asset('market-olive-oil.png'),
                        'tone' => 'sand',
                    ],
                    [
                        'eyebrow' => 'New Arrivals',
                        'title' => 'Fresh picks for the weekly shop',
                        'text' => 'Bakery, drinks and household',
                        'cta' => 'Shop Now',
                        'href' => route('store.coming-soon', [$store->slug, 'new-arrivals']),
                        'image' => $asset('market-sourdough-loaf.png'),
                        'tone' => 'sky',
                    ],
                    [
                        'eyebrow' => 'Big Deals',
                        'title' => 'Up to 20% Off',
                        'text' => 'Limited demonstration offer',
                        'cta' => 'Shop Now',
                        'href' => route('store.coming-soon', [$store->slug, 'sale']),
                        'image' => $asset('market-single-origin-coffee.png'),
                        'tone' => 'mist',
                    ],
                ],
                'stack' => [
                    [
                        'title' => 'Pantry',
                        'text' => 'Min. 10% Off',
                        'cta' => 'Shop Now',
                        'href' => route('store.category', [$store->slug, 'pantry']),
                        'image' => $asset('market-basmati-rice.png'),
                    ],
                    [
                        'title' => 'Beverages',
                        'text' => 'Min. 15% Off',
                        'cta' => 'Shop Now',
                        'href' => route('store.category', [$store->slug, 'beverages']),
                        'image' => $asset('market-cold-pressed-juice.png'),
                    ],
                    [
                        'title' => 'Household',
                        'text' => 'Min. 20% Off',
                        'cta' => 'Shop Now',
                        'href' => route('store.category', [$store->slug, 'household']),
                        'image' => $asset('market-cotton-towel-set.png'),
                    ],
                ],
            ],
            default => [
                'wide' => [
                    [
                        'eyebrow' => 'Summer Sale',
                        'title' => 'Up to 50% Off',
                        'text' => 'On selected items',
                        'cta' => 'Shop Now',
                        'href' => route('store.coming-soon', [$store->slug, 'sale']),
                        'image' => $asset('fashion-wool-overcoat.png'),
                        'tone' => 'sand',
                    ],
                    [
                        'eyebrow' => 'New Arrivals',
                        'title' => 'Check out latest trends for you',
                        'text' => 'Edited pieces for the season',
                        'cta' => 'Shop Now',
                        'href' => route('store.coming-soon', [$store->slug, 'new-arrivals']),
                        'image' => $asset('fashion-cotton-poplin-shirt.png'),
                        'tone' => 'sky',
                    ],
                    [
                        'eyebrow' => 'Big Deals',
                        'title' => 'Up to 70% Off',
                        'text' => 'Limited time offer',
                        'cta' => 'Shop Now',
                        'href' => route('store.coming-soon', [$store->slug, 'sale']),
                        'image' => $asset('fashion-strappy-heeled-sandal.png'),
                        'tone' => 'mist',
                    ],
                ],
                'stack' => [
                    [
                        'title' => 'Handbags',
                        'text' => 'Min. 40% Off',
                        'cta' => 'Shop Now',
                        'href' => route('store.category', [$store->slug, 'accessories']),
                        'image' => $asset('fashion-leather-tote.png'),
                    ],
                    [
                        'title' => 'Jewellery',
                        'text' => 'Min. 30% Off',
                        'cta' => 'Shop Now',
                        'href' => route('store.category', [$store->slug, 'accessories']),
                        'image' => $asset('fashion-beaded-collar-necklace.png'),
                    ],
                    [
                        'title' => 'Sneakers',
                        'text' => 'Min. 25% Off',
                        'cta' => 'Shop Now',
                        'href' => route('store.category', [$store->slug, 'footwear']),
                        'image' => $asset('fashion-everyday-sneaker.png'),
                    ],
                ],
            ],
        };
    }
}
