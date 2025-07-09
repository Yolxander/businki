<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\IntakeResponse;
use App\Models\Proposal;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Mockery;

class AIGenerationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_generate_proposal_endpoint_requires_authentication()
    {
        $response = $this->postJson('/api/intake-responses/1/generate-proposal');

        $response->assertStatus(401);
    }

    public function test_generate_proposal_endpoint_validates_input()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/intake-responses/999/generate-proposal', [
                'include_deliverables' => 'invalid_boolean'
            ]);

        $response->assertStatus(422);
    }

    public function test_generate_project_endpoint_requires_authentication()
    {
        $response = $this->postJson('/api/proposals/1/generate-project');

        $response->assertStatus(401);
    }

    public function test_generate_personal_project_endpoint_requires_authentication()
    {
        $response = $this->postJson('/api/projects/generate-personal-ai-project', [
            'project_type' => 'test',
            'description' => 'test description'
        ]);

        $response->assertStatus(401);
    }

    public function test_generate_tasks_endpoint_requires_authentication()
    {
        $response = $this->postJson('/api/projects/1/generate-tasks', [
            'project_description' => 'test',
            'project_scope' => 'test scope'
        ]);

        $response->assertStatus(401);
    }

    public function test_generate_personal_tasks_endpoint_requires_authentication()
    {
        $response = $this->postJson('/api/projects/generate-personal-tasks', [
            'project_type' => 'test',
            'project_title' => 'test title',
            'description' => 'test description'
        ]);

        $response->assertStatus(401);
    }

    public function test_generate_personal_project_validates_required_fields()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/projects/generate-personal-ai-project', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['project_type', 'description']);
    }

    public function test_generate_tasks_validates_required_fields()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/projects/1/generate-tasks', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['project_description', 'project_scope']);
    }

    public function test_generate_personal_tasks_validates_required_fields()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/projects/generate-personal-tasks', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['project_type', 'project_title', 'description']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
