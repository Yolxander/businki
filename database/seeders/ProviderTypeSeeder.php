<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProviderTypeSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('provider_types')->insert([
            [
                'name' => 'Web & Software Development',
                'description' => 'Web development, mobile apps, software engineering, and technical implementation',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Other',
                'description' => 'Other professional services not listed in the categories above',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
