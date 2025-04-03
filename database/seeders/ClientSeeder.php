<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $userId = DB::table('users')->value('id');
        $providerId = DB::table('providers')->value('id');

        DB::table('clients')->insert([
            [
                'name' => 'Infinity Property Management',
                'email' => 'contact@infinitypm.com',
                'phone' => '+1 (416) 222-3344',
                'address' => '123 King St W, Toronto, ON',
                'description' => 'Client focused on residential property management.',
                'provider_id' => $providerId,
                'user_id' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Hotel Daleese',
                'email' => 'info@daleesehotel.com',
                'phone' => '+1 (647) 555-6789',
                'address' => '456 Queen St E, Toronto, ON',
                'description' => 'Boutique hotel client for web and booking platform.',
                'provider_id' => $providerId,
                'user_id' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
