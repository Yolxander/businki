<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $projectId = DB::table('projects')->value('id');
        $providerId = DB::table('providers')->value('id');

        DB::table('tasks')->insert([
            [
                'title' => 'Set up project repo',
                'description' => 'Create the GitHub repo and initialize with README and .gitignore.',
                'status' => 'todo',
                'priority' => 'high',
                'category' => 'setup',
                'due_date' => now()->addDays(3),
                'project_id' => $projectId,
                'provider_id' => $providerId,
                'completed' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Build landing page',
                'description' => 'Create the Jointri landing page with Tailwind and Vue components.',
                'status' => 'in_progress',
                'priority' => 'medium',
                'category' => 'frontend',
                'due_date' => now()->addDays(10),
                'project_id' => $projectId,
                'provider_id' => $providerId,
                'completed' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
