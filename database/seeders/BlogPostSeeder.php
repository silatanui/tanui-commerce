<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use App\Models\Store;
use App\Support\Slugs;
use Illuminate\Database\Seeder;

class BlogPostSeeder extends Seeder
{
    public function run(): void
    {
        $stories = [
            'fashion' => [
                'title' => 'The mid-season edit: quieter pieces, sharper lines',
                'excerpt' => 'A demonstration look at how Tanui Fashion frames transitional wardrobes without the noise.',
                'cover' => 'demo-assets/stores/fashion-mark.svg',
                'body' => '<p>This season’s demonstration edit leans into calm silhouettes and fabric stories you can explain in one sentence. The goal is clarity: fewer competing claims, stronger merchandising hierarchy.</p><p>Use the journal to introduce drops, care notes and styling cues. In Tanui Fashion, news posts sit beside the shop so discovery never feels like a detour.</p><p>AI-assisted drafting in admin helps merchandisers move from blank page to publishable outline, then refine tone for the brand.</p>',
            ],
            'electronics' => [
                'title' => 'Desk setup week: power, ports and practical upgrades',
                'excerpt' => 'Tanui Electronics walks through a sample workstation story built for demo browsing and comparison.',
                'cover' => 'demo-assets/stores/electronics-mark.svg',
                'body' => '<p>Workstations sell through clarity. This demonstration post groups accessories by job-to-be-done: charge, connect, calm the cable run.</p><p>Pair news with comparison tools and bestsellers so shoppers can rehearse decisions without leaving the storefront narrative.</p><p>Editors can draft technical-friendly copy with AI suggestions, then keep specifications accurate in the catalogue itself.</p>',
            ],
            'home' => [
                'title' => 'Sunday reset: table, light and lived-in calm',
                'excerpt' => 'Tanui Home shares a sample ritual for refreshing rooms with purposeful, demonstration-ready pieces.',
                'cover' => 'demo-assets/stores/home-mark.svg',
                'body' => '<p>Home merchandising thrives on ritual. This sample journal entry frames a Sunday reset: clear the table, soften the light, keep one object with weight.</p><p>Use posts to teach room stories rather than dumping product lists. The catalogue still does the converting; news builds intent.</p><p>Admin AI fill can sketch the mood paragraph while you choose the hero products that belong in the scene.</p>',
            ],
            'market' => [
                'title' => 'Market basket notes: staples worth restocking first',
                'excerpt' => 'Tanui Market demonstrates how a simple restock story can guide weekly shopping without overwhelm.',
                'cover' => 'demo-assets/stores/market-mark.svg',
                'body' => '<p>Everyday retail needs rhythm. This demonstration post highlights a short restock list: pantry anchors, fresh add-ons, one small treat.</p><p>News keeps the market storefront feeling alive between campaigns, while inventory and coupons handle the mechanics.</p><p>Draft faster with AI suggestions, then adjust for seasonal availability in your catalogue.</p>',
            ],
        ];

        foreach (Store::query()->orderBy('id')->get() as $store) {
            $story = $stories[$store->slug] ?? [
                'title' => 'Welcome to the '.$store->name.' journal',
                'excerpt' => 'A sample news post for the '.$store->name.' demonstration storefront.',
                'cover' => 'demo-assets/placeholder.svg',
                'body' => '<p>This is a seeded news post for '.$store->name.'. Use admin News to edit or create more stories.</p>',
            ];

            $existing = BlogPost::query()
                ->forStore($store->id)
                ->where('title', $story['title'])
                ->first();

            $payload = [
                'title' => $story['title'],
                'slug' => $existing?->slug ?: Slugs::unique(BlogPost::class, $story['title'], $store->id),
                'excerpt' => $story['excerpt'],
                'body' => $story['body'],
                'cover_image' => $story['cover'],
                'status' => 'published',
                'published_at' => now()->subDays(2),
            ];

            if ($existing) {
                $existing->update($payload);
            } else {
                BlogPost::query()->create([
                    'store_id' => $store->id,
                    ...$payload,
                ]);
            }
        }
    }
}
