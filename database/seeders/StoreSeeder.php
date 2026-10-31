<?php

namespace Database\Seeders;

use App\Models\Store;
use App\Models\StoreBanner;
use App\Support\DemoArtwork;
use Illuminate\Database\Seeder;

class StoreSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = require database_path('seeders/data/catalog.php');

        foreach ($catalog as $slug => $data) {
            $store = Store::query()->create($data['store'] + [
                'status' => 'active',
                'logo' => DemoArtwork::resolve('demo-assets/stores/'.$slug, $data['store']['name'], 'TANUI COMMERCE', $slug, true),
                'favicon' => DemoArtwork::resolve('demo-assets/stores/'.$slug.'-mark', strtoupper(substr($slug, 0, 1)), 'TANUI', $slug),
            ]);

            foreach ($data['settings'] as $key => $value) {
                $store->settings()->create([
                    'setting_key' => $key,
                    'setting_value' => (string) $value,
                ]);
            }

            foreach ($data['banners'] as $index => $banner) {
                StoreBanner::query()->create([
                    'store_id' => $store->id,
                    'title' => $banner['title'],
                    'subtitle' => $banner['subtitle'],
                    'position' => $banner['position'],
                    'sort_order' => $index,
                    'status' => 'active',
                    'link' => '/store/'.$slug.'/shop',
                    'image' => DemoArtwork::resolve(
                        'demo-assets/banners/'.$slug.'-'.$index,
                        $banner['title'],
                        strtoupper($slug),
                        $slug,
                        true
                    ),
                ]);
            }
        }
    }
}
