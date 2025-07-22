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
        // Get existing client IDs and user ID
        $clientIds = DB::table('clients')->pluck('id')->toArray();
        $userId = DB::table('users')->value('id');

        $projects = [
            [
                'name' => 'Website Redesign',
                'description' => 'Complete redesign of the company website with modern UI/UX',
                'status' => 'in-progress',
                'current_phase' => 'Development',
                'priority' => 'high',
                'progress' => 65,
                'kickoff_date' => now()->subDays(30),
                'start_date' => now()->subDays(30),
                'due_date' => now()->addDays(15),
                'notes' => 'Client is very satisfied with the progress so far',
                'color' => 'bg-blue-100',
                'client_id' => $clientIds[0] ?? null,
                'proposal_id' => null, // Projects can exist without proposals
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Brand Identity Package',
                'description' => 'Complete brand identity including logo, colors, and guidelines',
                'status' => 'completed',
                'current_phase' => 'Completed',
                'priority' => 'medium',
                'progress' => 100,
                'kickoff_date' => now()->subDays(60),
                'start_date' => now()->subDays(60),
                'due_date' => now()->subDays(10),
                'notes' => 'Project completed successfully, client approved all deliverables',
                'color' => 'bg-green-200',
                'client_id' => $clientIds[1] ?? null,
                'proposal_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Mobile App Development',
                'description' => 'iOS and Android mobile application for client services',
                'status' => 'planned',
                'current_phase' => 'Planning',
                'priority' => 'high',
                'progress' => 0,
                'kickoff_date' => now()->addDays(10),
                'start_date' => now()->addDays(10),
                'due_date' => now()->addDays(90),
                'notes' => 'Awaiting client approval to begin development phase',
                'color' => 'bg-purple-100',
                'client_id' => $clientIds[2] ?? null,
                'proposal_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'E-commerce Platform',
                'description' => 'Full e-commerce solution with payment processing and inventory management',
                'status' => 'in-progress',
                'current_phase' => 'Design',
                'priority' => 'medium',
                'progress' => 35,
                'kickoff_date' => now()->subDays(15),
                'start_date' => now()->subDays(15),
                'due_date' => now()->addDays(45),
                'notes' => 'Design phase is progressing well, client feedback has been positive',
                'color' => 'bg-orange-100',
                'client_id' => $clientIds[3] ?? null,
                'proposal_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        DB::table('projects')->insert($projects);
    }
}
