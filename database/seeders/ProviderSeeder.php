<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProviderSeeder extends Seeder
{
    public function run(): void
    {
        $webDevTypeId = DB::table('provider_types')->where('name', 'Web & Software Development')->value('id');
        $otherTypeId = DB::table('provider_types')->where('name', 'Other')->value('id');

        // Get a user ID from the users table (make sure you seed or have one)
        $userId = DB::table('users')->value('id');

        DB::table('providers')->insert([
            [
                'user_id' => $userId,
                'provider_type_id' => $webDevTypeId,
                'name' => 'Sempre Studios',
                'email' => 'hello@semprestudios.com',
                'phone' => '+1 (416) 123-4567',
                'website' => 'https://semprestudios.com',
                'description' => 'A digital agency focused on custom web apps, branding, and technical execution.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $userId,
                'provider_type_id' => $otherTypeId,
                'name' => 'Creative Experts Co.',
                'email' => 'contact@creativeexperts.com',
                'phone' => null,
                'website' => null,
                'description' => 'Professional services across various industries.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
