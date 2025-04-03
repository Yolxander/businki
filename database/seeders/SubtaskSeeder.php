<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubtaskSeeder extends Seeder
{
    public function run(): void
    {
        $taskId = DB::table('tasks')->value('id');
        $providerId = DB::table('providers')->value('id');

        DB::table('subtasks')->insert([
            [
                'title' => 'Initialize git',
                'description' => 'Run git init in project folder',
                'status' => 'todo',
                'completed' => false,
                'task_id' => $taskId,
                'provider_id' => $providerId,
                'code_snippet' => 'git init',
                'language' => 'bash',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Add Tailwind to project',
                'description' => 'Install and configure TailwindCSS',
                'status' => 'in_progress',
                'completed' => false,
                'task_id' => $taskId,
                'provider_id' => $providerId,
                'code_snippet' => 'npm install -D tailwindcss postcss autoprefixer',
                'language' => 'bash',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
