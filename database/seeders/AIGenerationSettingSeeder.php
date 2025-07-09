<?php

namespace Database\Seeders;

use App\Models\AIGenerationSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AIGenerationSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'name' => 'default',
                'model' => 'gpt-4',
                'temperature' => 0.7,
                'max_tokens' => 4000,
                'top_p' => 1.0,
                'frequency_penalty' => 0,
                'presence_penalty' => 0,
                'system_prompt' => 'You are a helpful AI assistant that generates high-quality content for business applications.',
                'is_active' => true,
                'description' => 'Default settings for general AI content generation',
            ],
            [
                'name' => 'proposal',
                'model' => 'gpt-4',
                'temperature' => 0.3,
                'max_tokens' => 6000,
                'top_p' => 0.9,
                'frequency_penalty' => 0.1,
                'presence_penalty' => 0.1,
                'system_prompt' => 'You are an expert business proposal writer. Create detailed, professional proposals with clear scope, timeline, and pricing.',
                'is_active' => true,
                'description' => 'Optimized for generating detailed business proposals',
            ],
            [
                'name' => 'project',
                'model' => 'gpt-4',
                'temperature' => 0.4,
                'max_tokens' => 5000,
                'top_p' => 0.9,
                'frequency_penalty' => 0.1,
                'presence_penalty' => 0.1,
                'system_prompt' => 'You are a project management expert. Create comprehensive project plans with tasks, milestones, and deliverables.',
                'is_active' => true,
                'description' => 'Optimized for project planning and management',
            ],
            [
                'name' => 'task',
                'model' => 'gpt-3.5-turbo',
                'temperature' => 0.5,
                'max_tokens' => 2000,
                'top_p' => 0.9,
                'frequency_penalty' => 0,
                'presence_penalty' => 0,
                'system_prompt' => 'You are a task management specialist. Create clear, actionable task descriptions with requirements and acceptance criteria.',
                'is_active' => true,
                'description' => 'Optimized for task creation and management',
            ],
            [
                'name' => 'creative',
                'model' => 'gpt-4',
                'temperature' => 0.9,
                'max_tokens' => 4000,
                'top_p' => 0.8,
                'frequency_penalty' => 0.2,
                'presence_penalty' => 0.2,
                'system_prompt' => 'You are a creative content writer. Generate engaging, innovative content with a creative flair.',
                'is_active' => true,
                'description' => 'Optimized for creative content generation',
            ],
            [
                'name' => 'technical',
                'model' => 'gpt-4',
                'temperature' => 0.2,
                'max_tokens' => 4000,
                'top_p' => 0.9,
                'frequency_penalty' => 0,
                'presence_penalty' => 0,
                'system_prompt' => 'You are a technical writer. Create precise, accurate technical documentation and specifications.',
                'is_active' => true,
                'description' => 'Optimized for technical documentation and specifications',
            ],
        ];

        foreach ($settings as $setting) {
            AIGenerationSetting::updateOrCreate(
                ['name' => $setting['name']],
                $setting
            );
        }
    }
}
