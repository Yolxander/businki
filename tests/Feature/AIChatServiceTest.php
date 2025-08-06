<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Services\AIChatService;
use App\Services\OpenAIService;
use App\Services\ContextAwareService;
use App\Services\AIMLAPIService;
use App\Services\ClientService;
use App\Services\AIIntentDetectionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Mockery;

class AIChatServiceTest extends TestCase
{
    use RefreshDatabase;

    private AIChatService $aiChatService;
    private User $user;
    private Chat $chat;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user
        $this->user = User::factory()->create();

        // Create a test chat
        $this->chat = Chat::create([
            'user_id' => $this->user->id,
            'type' => 'projects',
            'title' => 'Test Project Chat',
            'last_activity_at' => now(),
        ]);

        // Mock the AI services
        $openAIService = Mockery::mock(OpenAIService::class);
        $contextAwareService = Mockery::mock(ContextAwareService::class);
        $aimlapiService = Mockery::mock(AIMLAPIService::class);
        $clientService = Mockery::mock(ClientService::class);
        $aiIntentDetectionService = Mockery::mock(AIIntentDetectionService::class);

        // Mock OpenAI service responses
        $openAIService->shouldReceive('generateChatCompletionWithParams')
            ->andReturn([
                'content' => 'This is a test AI response for project management.',
                'usage' => ['total_tokens' => 150],
                'cost' => 0.003
            ]);

        // Mock context aware service
        $contextAwareService->shouldReceive('refreshUserContext')->andReturn(null);
        $contextAwareService->shouldReceive('getPlatformContext')->andReturn([
            'platform_description' => 'Business Management Platform',
            'available_tables' => ['projects', 'tasks', 'clients'],
            'supported_metrics' => ['projects' => ['all', 'active']]
        ]);
        $contextAwareService->shouldReceive('getUserContext')->andReturn([
            'user_stats' => ['total_projects' => 5],
            'user_permissions' => ['projects' => true]
        ]);

        // Mock AI Intent Detection Service
        $aiIntentDetectionService->shouldReceive('detectIntent')
            ->andReturn([
                'type' => 'general',
                'action' => 'none',
                'confidence' => 0.3,
                'entities' => []
            ]);

        $this->aiChatService = new AIChatService(
            $openAIService,
            $contextAwareService,
            $aimlapiService,
            $clientService,
            $aiIntentDetectionService
        );

        // Mock Log facade for all tests
        Log::shouldReceive('info')->andReturn(null);
        Log::shouldReceive('error')->andReturn(null);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_it_can_process_a_user_message()
    {
        $userMessage = 'Show me all active projects';

        $result = $this->aiChatService->processMessage($this->chat, $userMessage);

        $this->assertTrue($result['success']);
        $this->assertStringContainsString('test AI response', $result['response']);
        $this->assertArrayHasKey('metadata', $result);
        $this->assertEquals('projects', $result['metadata']['chat_type']);
    }

    public function test_it_handles_ai_service_errors_gracefully()
    {
        // Mock OpenAI service to throw an exception
        $openAIService = Mockery::mock(OpenAIService::class);
        $contextAwareService = Mockery::mock(ContextAwareService::class);
        $aimlapiService = Mockery::mock(AIMLAPIService::class);
        $clientService = Mockery::mock(ClientService::class);
        $aiIntentDetectionService = Mockery::mock(AIIntentDetectionService::class);

        $openAIService->shouldReceive('generateChatCompletionWithParams')
            ->andThrow(new \Exception('AI service unavailable'));

        $contextAwareService->shouldReceive('refreshUserContext')->andReturn(null);
        $contextAwareService->shouldReceive('getPlatformContext')->andReturn([]);
        $contextAwareService->shouldReceive('getUserContext')->andReturn([]);

        // Mock AI Intent Detection Service
        $aiIntentDetectionService->shouldReceive('detectIntent')
            ->andReturn([
                'type' => 'general',
                'action' => 'none',
                'confidence' => 0.3,
                'entities' => []
            ]);

        $aiChatService = new AIChatService(
            $openAIService,
            $contextAwareService,
            $aimlapiService,
            $clientService,
            $aiIntentDetectionService
        );

                $result = $aiChatService->processMessage($this->chat, 'Test message');

        // The service should handle the error gracefully and still return success
        $this->assertTrue($result['success']);
        $this->assertStringContainsString('error', $result['response']);
        $this->assertArrayHasKey('metadata', $result);
    }

    public function test_it_provides_chat_type_suggestions()
    {
        $suggestions = $this->aiChatService->getChatTypeSuggestions('projects');

        $this->assertIsArray($suggestions);
        $this->assertNotEmpty($suggestions);
        $this->assertContains('List all active projects', $suggestions);
    }
}
