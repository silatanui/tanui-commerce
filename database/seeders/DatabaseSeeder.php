<?php

namespace Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Model::unguarded(function () {
            $this->call([
                StoreSeeder::class,
                CatalogSeeder::class,
                UserSeeder::class,
                CommerceSeeder::class,
                BlogPostSeeder::class,
            ]);
        });
    }
}
