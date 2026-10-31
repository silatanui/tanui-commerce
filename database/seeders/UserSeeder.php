<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->create([
            'name' => 'Tanui Admin',
            'email' => 'admin@tanuicommerce.test',
            'phone' => '+254 700 100 200',
            'password' => 'password',
            'role' => 'admin',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $amina = User::query()->create([
            'name' => 'Amina Otieno',
            'email' => 'amina@example.test',
            'phone' => '+254 711 000 111',
            'password' => 'password',
            'role' => 'customer',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $david = User::query()->create([
            'name' => 'David Kariuki',
            'email' => 'david@example.test',
            'phone' => '+254 722 000 222',
            'password' => 'password',
            'role' => 'customer',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $amina->addresses()->create([
            'full_name' => 'Amina Otieno',
            'phone' => '+254 711 000 111',
            'address_line' => '12 Sample Lane, Kilimani',
            'city' => 'Nairobi',
            'postal_code' => '00100',
            'country' => 'Kenya',
            'is_default' => true,
        ]);

        $david->addresses()->create([
            'full_name' => 'David Kariuki',
            'phone' => '+254 722 000 222',
            'address_line' => '4 Demonstration Court, Westlands',
            'city' => 'Nairobi',
            'postal_code' => '00100',
            'country' => 'Kenya',
            'is_default' => true,
        ]);

        unset($admin);
    }
}
