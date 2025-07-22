<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubtaskSeeder extends Seeder
{
    public function run(): void
    {
        $taskIds = DB::table('tasks')->pluck('id')->toArray();

        $subtasks = [
            [
                'description' => 'Initialize git repository and set up remote',
                'status' => 'todo',
                'task_id' => $taskIds[0] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'description' => 'Create README.md with project overview',
                'status' => 'done',
                'task_id' => $taskIds[0] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'description' => 'Set up .gitignore file',
                'status' => 'done',
                'task_id' => $taskIds[0] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'description' => 'Install and configure TailwindCSS',
                'status' => 'in_progress',
                'task_id' => $taskIds[1] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'description' => 'Create base component library',
                'status' => 'todo',
                'task_id' => $taskIds[1] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'description' => 'Design color palette and typography',
                'status' => 'todo',
                'task_id' => $taskIds[1] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'description' => 'Create wireframes for landing page',
                'status' => 'todo',
                'task_id' => $taskIds[2] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'description' => 'Implement responsive navigation',
                'status' => 'todo',
                'task_id' => $taskIds[2] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'description' => 'Design database schema',
                'status' => 'done',
                'task_id' => $taskIds[4] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'description' => 'Create database migrations',
                'status' => 'done',
                'task_id' => $taskIds[4] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        DB::table('subtasks')->insert($subtasks);
    }
}
