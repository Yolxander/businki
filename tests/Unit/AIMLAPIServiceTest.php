<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\AIMLAPIService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AIMLAPIServiceTest extends TestCase
{
    use RefreshDatabase;

    private AIMLAPIService $aimlapiService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->aimlapiService = new AIMLAPIService();
    }

    public function test_service_can_be_instantiated()
    {
        $this->assertInstanceOf(AIMLAPIService::class, $this->aimlapiService);
    }

    public function test_get_configuration_returns_array()
    {
        $config = $this->aimlapiService->getConfiguration();

        $this->assertIsArray($config);
        $this->assertArrayHasKey('model', $config);
        $this->assertArrayHasKey('max_tokens', $config);
        $this->assertArrayHasKey('temperature', $config);
        $this->assertArrayHasKey('top_p', $config);
        $this->assertArrayHasKey('frequency_penalty', $config);
        $this->assertArrayHasKey('presence_penalty', $config);
    }

    public function test_update_configuration_works()
    {
        $originalConfig = $this->aimlapiService->getConfiguration();

        $newConfig = [
            'temperature' => 0.8,
            'max_tokens' => 1000,
            'top_p' => 0.95
        ];

        $this->aimlapiService->updateConfiguration($newConfig);

        $updatedConfig = $this->aimlapiService->getConfiguration();

        $this->assertEquals(0.8, $updatedConfig['temperature']);
        $this->assertEquals(1000, $updatedConfig['max_tokens']);
        $this->assertEquals(0.95, $updatedConfig['top_p']);
    }

    public function test_build_proposal_prompt_returns_string()
    {
        $intakeData = [
            'client_name' => 'John Doe',
            'company_name' => 'Test Company',
            'email' => 'john@test.com',
            'project_type' => 'Web Development',
            'description' => 'A simple website',
            'budget' => '$5000',
            'timeline' => '2 months'
        ];

        $prompt = $this->aimlapiService->buildProposalPrompt($intakeData, true, true, true);

        $this->assertIsString($prompt);
        $this->assertStringContainsString('John Doe', $prompt);
        $this->assertStringContainsString('Test Company', $prompt);
        $this->assertStringContainsString('Web Development', $prompt);
    }

    public function test_build_project_prompt_returns_string()
    {
        $proposalData = [
            'title' => 'Test Project',
            'scope' => 'Build a website',
            'deliverables' => ['Homepage', 'Contact Form'],
            'price' => 5000
        ];

        $prompt = $this->aimlapiService->buildProjectPrompt($proposalData, true, true);

        $this->assertIsString($prompt);
        $this->assertStringContainsString('Test Project', $prompt);
        $this->assertStringContainsString('Build a website', $prompt);
    }

    public function test_build_tasks_prompt_returns_string()
    {
        $projectData = [
            'title' => 'Test Project',
            'current_phase' => 'Planning',
            'notes' => 'Project notes'
        ];

        $prompt = $this->aimlapiService->buildTasksPrompt($projectData, 5, false);

        $this->assertIsString($prompt);
        $this->assertStringContainsString('Test Project', $prompt);
        $this->assertStringContainsString('Planning', $prompt);
    }
}
