<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing client and provider UUIDs
        $clientId = DB::table('users')->value('id');
        $providerId = DB::table('providers')->value('id');

        DB::table('projects')->insert([
            [
                'name' => 'Jointri MVP',
                'description' => 'Building the MVP for the Jointri platform',
                'status' => 'In Progress',
                'start_date' => now(),
                'due_date' => now()->addDays(30),
                'client_id' => $clientId,
                'provider_id' => $providerId,
                'color' => 'bg-blue-100',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sempre Site Refresh',
                'description' => 'Revamping the agency website and CMS',
                'status' => 'Planning',
                'start_date' => now()->subDays(5),
                'due_date' => now()->addDays(20),
                'client_id' => $clientId,
                'provider_id' => $providerId,
                'color' => 'bg-green-200',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
