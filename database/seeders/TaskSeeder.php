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
        $projectIds = DB::table('projects')->pluck('id')->toArray();
        $userId = DB::table('users')->value('id');

        $tasks = [
            [
                'title' => 'Set up project repository',
                'description' => 'Create the GitHub repo and initialize with README and .gitignore.',
                'status' => 'todo',
                'priority' => 'high',
                'due_date' => now()->addDays(3),
                'project_id' => $projectIds[0] ?? null,
                'assigned_to' => $userId,
                'estimated_hours' => 2.00,
                'tags' => json_encode(['setup', 'repository']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Design system setup',
                'description' => 'Create the design system with Tailwind and component library.',
                'status' => 'in_progress',
                'priority' => 'medium',
                'due_date' => now()->addDays(7),
                'project_id' => $projectIds[0] ?? null,
                'assigned_to' => $userId,
                'estimated_hours' => 8.00,
                'tags' => json_encode(['design', 'frontend']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Build landing page',
                'description' => 'Create the main landing page with modern UI components.',
                'status' => 'todo',
                'priority' => 'high',
                'due_date' => now()->addDays(10),
                'project_id' => $projectIds[1] ?? null,
                'assigned_to' => $userId,
                'estimated_hours' => 12.00,
                'tags' => json_encode(['frontend', 'landing']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'API development',
                'description' => 'Develop the backend API endpoints for the application.',
                'status' => 'todo',
                'priority' => 'high',
                'due_date' => now()->addDays(14),
                'project_id' => $projectIds[2] ?? null,
                'assigned_to' => $userId,
                'estimated_hours' => 20.00,
                'tags' => json_encode(['backend', 'api']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Database design',
                'description' => 'Design and implement the database schema.',
                'status' => 'done',
                'priority' => 'high',
                'due_date' => now()->subDays(5),
                'project_id' => $projectIds[3] ?? null,
                'assigned_to' => $userId,
                'estimated_hours' => 6.00,
                'tags' => json_encode(['database', 'backend']),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        DB::table('tasks')->insert($tasks);
    }
}
