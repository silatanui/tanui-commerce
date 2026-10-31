<?php

namespace App\Support;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Support\Collection;

class StoreLookbook
{
    /**
     * @return array{title: string, lede: string, looks: list<array<string, mixed>>}
     */
    public static function forStore(Store $store): array
    {
        $products = Product::query()
            ->forStore($store)
            ->published()
            ->with(['brand', 'images', 'variants'])
            ->withRatings()
            ->get()
            ->keyBy('slug');

        return match ($store->slug) {
            'fashion' => self::fashion($store, $products),
            'electronics' => self::electronics($store, $products),
            'home' => self::home($store, $products),
            'market' => self::market($store, $products),
            default => self::generic($store, $products),
        };
    }

    /**
     * @param  Collection<string, Product>  $products
     * @return array{title: string, lede: string, looks: list<array<string, mixed>>}
     */
    private static function fashion(Store $store, Collection $products): array
    {
        return [
            'title' => 'Season lookbook',
            'lede' => 'Editorial sample layouts for '.$store->name.' — styled from the demonstration catalogue. Images are product assets, not paid campaign photography.',
            'looks' => [
                self::look(
                    'Ivory hour',
                    'A clean column silhouette with soft metal and a structured tote.',
                    ['ivory-column-dress', 'beaded-collar-necklace', 'leather-tote'],
                    $products,
                    'tall'
                ),
                self::look(
                    'City knit',
                    'Merino ease, everyday sneakers and a bag that works past noon.',
                    ['merino-crew-knit', 'everyday-sneaker', 'leather-tote'],
                    $products,
                    'split'
                ),
                self::look(
                    'Tailored black',
                    'Coat weight for Nairobi evenings — shirt underneath, heel optional.',
                    ['wool-overcoat', 'cotton-poplin-shirt', 'strappy-heeled-sandal'],
                    $products,
                    'wide'
                ),
            ],
        ];
    }

    /**
     * @param  Collection<string, Product>  $products
     * @return array{title: string, lede: string, looks: list<array<string, mixed>>}
     */
    private static function electronics(Store $store, Collection $products): array
    {
        return [
            'title' => 'Desk lookbook',
            'lede' => 'Sample kit pairings from the '.$store->name.' catalogue — for portfolio storytelling, not affiliate placements.',
            'looks' => [
                self::look('Focus stack', 'Phone, buds and a watch for a compact daily kit.', ['nimbus-phone-x', 'aria-buds-pro', 'smart-watch-s'], $products, 'split'),
                self::look('Studio lane', 'Laptop, headphones and keyboard for long sessions.', ['fieldnote-laptop-14', 'studio-headphones', 'mechanical-keyboard'], $products, 'wide'),
                self::look('Living room sound', 'Soundbar with camera kit for content evenings.', ['volt-soundbar', 'mirrorless-camera-kit'], $products, 'tall'),
            ],
        ];
    }

    /**
     * @param  Collection<string, Product>  $products
     * @return array{title: string, lede: string, looks: list<array<string, mixed>>}
     */
    private static function home(Store $store, Collection $products): array
    {
        return [
            'title' => 'Rooms lookbook',
            'lede' => 'Demonstration vignettes built from '.$store->name.' sample furniture and lighting.',
            'looks' => [
                self::look('Soft living', 'Sofa depth with a lounge chair and warm lamp light.', ['three-seat-linen-sofa', 'lounge-chair', 'ceramic-table-lamp'], $products, 'wide'),
                self::look('Quiet desk', 'Walnut desk, task chair and lamp for focused work.', ['walnut-desk', 'task-chair', 'ceramic-table-lamp'], $products, 'split'),
                self::look('Dinner set', 'Oak table with dining chairs for a calm evening table.', ['oak-dining-table', 'dining-chair'], $products, 'tall'),
            ],
        ];
    }

    /**
     * @param  Collection<string, Product>  $products
     * @return array{title: string, lede: string, looks: list<array<string, mixed>>}
     */
    private static function market(Store $store, Collection $products): array
    {
        return [
            'title' => 'Pantry lookbook',
            'lede' => 'Sample basket edits from '.$store->name.' — grocery storytelling with demonstration stock.',
            'looks' => [
                self::look('Morning table', 'Sourdough, coffee and juice for a weekend start.', ['sourdough-loaf', 'single-origin-coffee', 'cold-pressed-juice'], $products, 'split'),
                self::look('Cook’s shelf', 'Oil, rice and sparkling water for midweek cooking.', ['olive-oil', 'basmati-rice', 'sparkling-water'], $products, 'wide'),
                self::look('Bath reset', 'Towels and cream for a simple aftercare edit.', ['cotton-towel-set', 'hand-cream'], $products, 'tall'),
            ],
        ];
    }

    /**
     * @param  Collection<string, Product>  $products
     * @return array{title: string, lede: string, looks: list<array<string, mixed>>}
     */
    private static function generic(Store $store, Collection $products): array
    {
        $chunks = $products->values()->chunk(3)->take(3);
        $looks = [];
        $layouts = ['wide', 'split', 'tall'];

        foreach ($chunks as $index => $chunk) {
            $looks[] = [
                'title' => 'Edit '.($index + 1),
                'caption' => 'A sample grouping from the '.$store->name.' catalogue.',
                'layout' => $layouts[$index % 3],
                'products' => $chunk->values()->all(),
            ];
        }

        return [
            'title' => 'Lookbook',
            'lede' => 'Demonstration product compositions for '.$store->name.'.',
            'looks' => $looks,
        ];
    }

    /**
     * @param  list<string>  $slugs
     * @param  Collection<string, Product>  $products
     * @return array{title: string, caption: string, layout: string, products: list<Product>}
     */
    private static function look(string $title, string $caption, array $slugs, Collection $products, string $layout): array
    {
        $items = [];

        foreach ($slugs as $slug) {
            if ($products->has($slug)) {
                $items[] = $products->get($slug);
            }
        }

        return [
            'title' => $title,
            'caption' => $caption,
            'layout' => $layout,
            'products' => $items,
        ];
    }
}
