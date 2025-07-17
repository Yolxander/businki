<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AIProvider;
use App\Models\User;

class AIProviderSeeder extends Seeder
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

        // Clear existing providers for this user
        AIProvider::where('user_id', $user->id)->delete();

        // Create AI providers for different services
        $providers = [
            [
                'name' => 'Company AIMLAPI Account',
                'provider_type' => 'AIMLAPI',
                'api_key' => env('AIMLAPI_API_KEY', 'sk-demo-key-for-aimlapi'),
                'base_url' => 'https://api.aimlapi.com/v1',
                'status' => 'active',
                'settings' => [
                    'description' => 'Primary AIMLAPI account for the company',
                    'rate_limit' => 1000,
                    'timeout' => 30
                ]
            ],
            [
                'name' => 'Personal OpenAI Account',
                'provider_type' => 'OpenAI',
                'api_key' => env('OPENAI_API_KEY', 'sk-demo-key-for-openai'),
                'base_url' => 'https://api.openai.com/v1',
                'status' => 'active',
                'settings' => [
                    'description' => 'Personal OpenAI account for development',
                    'rate_limit' => 500,
                    'timeout' => 60
                ]
            ],
            [
                'name' => 'Team Anthropic Account',
                'provider_type' => 'Anthropic',
                'api_key' => env('ANTHROPIC_API_KEY', 'sk-demo-key-for-anthropic'),
                'base_url' => 'https://api.anthropic.com',
                'status' => 'active',
                'settings' => [
                    'description' => 'Team Claude account for content generation',
                    'rate_limit' => 200,
                    'timeout' => 45
                ]
            ],
            [
                'name' => 'Backup Google Account',
                'provider_type' => 'Google',
                'api_key' => env('GOOGLE_API_KEY', 'demo-key-for-google'),
                'base_url' => 'https://generativelanguage.googleapis.com',
                'status' => 'inactive',
                'settings' => [
                    'description' => 'Backup Google Gemini account',
                    'rate_limit' => 100,
                    'timeout' => 30
                ]
            ]
        ];

        foreach ($providers as $providerData) {
            AIProvider::create([
                'user_id' => $user->id,
                'name' => $providerData['name'],
                'provider_type' => $providerData['provider_type'],
                'api_key' => $providerData['api_key'],
                'base_url' => $providerData['base_url'],
                'status' => $providerData['status'],
                'settings' => $providerData['settings']
            ]);
        }

        $this->command->info('AI Providers seeded successfully!');
        $this->command->info('Created ' . count($providers) . ' AI providers for user: ' . $user->email);
    }
}
