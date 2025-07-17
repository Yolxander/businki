<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AIModel;
use App\Models\AIProvider;
use App\Models\User;

class AIModelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
        public function run(): void
    {
        // Get the first user or create one if none exists
        $user = User::first();

        if (!$user) {
            $user = User::factory()->create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
            ]);
        }

        // Get or create providers first
        $aimlapiProvider = AIProvider::where('user_id', $user->id)
            ->where('provider_type', 'AIMLAPI')
            ->first();

        $openaiProvider = AIProvider::where('user_id', $user->id)
            ->where('provider_type', 'OpenAI')
            ->first();

        $anthropicProvider = AIProvider::where('user_id', $user->id)
            ->where('provider_type', 'Anthropic')
            ->first();

        // Clear existing models for this user
        AIModel::where('user_id', $user->id)->delete();

        // Create AI models for different use cases
        $models = [
            [
                'name' => 'GPT-4o - Proposal Generator',
                'provider_id' => $aimlapiProvider->id,
                'model' => 'gpt-4o',
                'status' => 'active',
                'is_default' => true,
                'usage_count' => 1250,
                'last_used_at' => now()->subHours(2),
                'settings' => [
                    'temperature' => 0.7,
                    'max_tokens' => 2048,
                    'top_p' => 0.9,
                    'frequency_penalty' => 0.0,
                    'presence_penalty' => 0.0,
                    'description' => 'Primary model for generating professional business proposals with detailed scope, deliverables, and pricing.',
                    'use_cases' => ['proposal_generation', 'client_communication'],
                    'tags' => ['proposals', 'business', 'professional']
                ]
            ],
            [
                'name' => 'Claude-3 Sonnet - Project Planner',
                'provider_id' => $anthropicProvider->id,
                'model' => 'claude-3-sonnet',
                'status' => 'active',
                'is_default' => false,
                'usage_count' => 890,
                'last_used_at' => now()->subHours(5),
                'settings' => [
                    'temperature' => 0.6,
                    'max_tokens' => 3072,
                    'top_p' => 0.85,
                    'frequency_penalty' => 0.1,
                    'presence_penalty' => 0.1,
                    'description' => 'Specialized model for creating detailed project plans, timelines, and phase breakdowns.',
                    'use_cases' => ['project_planning', 'timeline_creation', 'phase_breakdown'],
                    'tags' => ['projects', 'planning', 'timeline']
                ]
            ],
            [
                'name' => 'GPT-4o Mini - Task Generator',
                'provider_id' => $aimlapiProvider->id,
                'model' => 'gpt-4o-mini',
                'status' => 'active',
                'is_default' => false,
                'usage_count' => 2100,
                'last_used_at' => now()->subHours(1),
                'settings' => [
                    'temperature' => 0.8,
                    'max_tokens' => 1024,
                    'top_p' => 0.95,
                    'frequency_penalty' => 0.0,
                    'presence_penalty' => 0.0,
                    'description' => 'Fast and efficient model for generating actionable tasks and subtasks with clear priorities.',
                    'use_cases' => ['task_generation', 'subtask_creation', 'priority_assignment'],
                    'tags' => ['tasks', 'subtasks', 'productivity']
                ]
            ],
            [
                'name' => 'Claude-3 Haiku - Content Writer',
                'provider_id' => $anthropicProvider->id,
                'model' => 'claude-3-haiku',
                'status' => 'active',
                'is_default' => false,
                'usage_count' => 567,
                'last_used_at' => now()->subDays(1),
                'settings' => [
                    'temperature' => 0.9,
                    'max_tokens' => 1536,
                    'top_p' => 0.9,
                    'frequency_penalty' => 0.2,
                    'presence_penalty' => 0.1,
                    'description' => 'Creative model for writing project descriptions, client communications, and marketing content.',
                    'use_cases' => ['content_writing', 'descriptions', 'communications'],
                    'tags' => ['content', 'writing', 'creative']
                ]
            ],
            [
                'name' => 'GPT-4 Turbo - Development Assistant',
                'provider_id' => $openaiProvider->id,
                'model' => 'gpt-4-turbo',
                'status' => 'active',
                'is_default' => false,
                'usage_count' => 234,
                'last_used_at' => now()->subDays(3),
                'settings' => [
                    'temperature' => 0.3,
                    'max_tokens' => 2048,
                    'top_p' => 0.8,
                    'frequency_penalty' => 0.0,
                    'presence_penalty' => 0.0,
                    'description' => 'Technical model for code generation, debugging assistance, and technical documentation.',
                    'use_cases' => ['code_generation', 'debugging', 'technical_docs'],
                    'tags' => ['code', 'technical', 'development']
                ]
            ]
        ];

        foreach ($models as $modelData) {
            AIModel::create([
                'user_id' => $user->id,
                'ai_provider_id' => $modelData['provider_id'],
                'name' => $modelData['name'],
                'model' => $modelData['model'],
                'status' => $modelData['status'],
                'is_default' => $modelData['is_default'],
                'usage_count' => $modelData['usage_count'],
                'last_used_at' => $modelData['last_used_at'],
                'settings' => $modelData['settings']
            ]);
        }

        $this->command->info('AI Models seeded successfully!');
        $this->command->info('Created ' . count($models) . ' AI models for user: ' . $user->email);
    }
}
