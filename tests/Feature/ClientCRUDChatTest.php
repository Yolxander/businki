<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Chat;
use App\Models\Client;
use App\Services\AIChatService;
use App\Services\ClientService;
use App\Services\AIIntentDetectionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ClientCRUDChatTest extends TestCase
{
    use RefreshDatabase;

    private AIChatService $aiChatService;
    private ClientService $clientService;
    private AIIntentDetectionService $intentDetectionService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clientService = app(ClientService::class);
        $this->intentDetectionService = app(AIIntentDetectionService::class);
        $this->aiChatService = app(AIChatService::class);
    }

    /** @test */
    public function it_can_detect_create_client_intent()
    {
        $message = "Create a new client named John Smith with email john@example.com";

        $intent = $this->intentDetectionService->detectIntent($message);

        $this->assertEquals('client', $intent['type']);
        $this->assertEquals('create', $intent['action']);
        $this->assertGreaterThanOrEqual(0.7, $intent['confidence']);
        $this->assertEquals('John', $intent['data']['first_name']);
        $this->assertEquals('Smith', $intent['data']['last_name']);
        $this->assertEquals('john@example.com', $intent['data']['email']);
    }

    /** @test */
    public function it_can_detect_read_client_intent()
    {
        $message = "Show client John Smith";

        $intent = $this->intentDetectionService->detectIntent($message);

        $this->assertEquals('client', $intent['type']);
        $this->assertEquals('read', $intent['action']);
        $this->assertGreaterThanOrEqual(0.7, $intent['confidence']);
        $this->assertEquals('john smith', $intent['data']['name']);
    }

    /** @test */
    public function it_can_detect_list_clients_intent()
    {
        $message = "List all clients";

        $intent = $this->intentDetectionService->detectIntent($message);

        $this->assertEquals('client', $intent['type']);
        $this->assertEquals('list', $intent['action']);
        $this->assertGreaterThanOrEqual(0.7, $intent['confidence']);
    }

    /** @test */
    public function it_can_create_client_via_chat()
    {
        $user = User::factory()->create();
        $chat = Chat::create([
            'user_id' => $user->id,
            'type' => 'clients',
            'title' => 'Client Management'
        ]);

        $message = "Create a new client named Jane Doe with email jane@example.com and phone 555-1234";

        $response = $this->aiChatService->processMessage($chat, $message);

        $this->assertTrue($response['success']);
        $this->assertStringContainsString('Client Jane Doe created successfully', $response['response']);
        $this->assertTrue($response['metadata']['client_intent_detected']);
        $this->assertEquals('create', $response['metadata']['client_action']);

        // Verify client was actually created
        $client = Client::where('email', 'jane@example.com')->first();
        $this->assertNotNull($client);
        $this->assertEquals('Jane', $client->first_name);
        $this->assertEquals('Doe', $client->last_name);
        $this->assertEquals('555-1234', $client->phone);
    }

    /** @test */
    public function it_can_read_client_via_chat()
    {
        $user = User::factory()->create();
        $chat = Chat::create([
            'user_id' => $user->id,
            'type' => 'clients',
            'title' => 'Client Management'
        ]);

        // Create a test client first
        $client = Client::create([
            'first_name' => 'Test',
            'last_name' => 'Client',
            'email' => 'test@example.com',
            'phone' => '555-9999',
            'company_name' => 'Test Company'
        ]);

        $message = "Show client test@example.com";

        $response = $this->aiChatService->processMessage($chat, $message);

        $this->assertTrue($response['success']);
        $this->assertStringContainsString('Found client: Test Client', $response['response']);
        $this->assertStringContainsString('test@example.com', $response['response']);
        $this->assertTrue($response['metadata']['client_intent_detected']);
        $this->assertEquals('read', $response['metadata']['client_action']);
    }

    /** @test */
    public function it_can_list_clients_via_chat()
    {
        $user = User::factory()->create();
        $chat = Chat::create([
            'user_id' => $user->id,
            'type' => 'clients',
            'title' => 'Client Management'
        ]);

        // Create some test clients
        Client::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com'
        ]);

        Client::create([
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane@example.com'
        ]);

        $message = "List all clients";

        $response = $this->aiChatService->processMessage($chat, $message);

        $this->assertTrue($response['success']);
        $this->assertStringContainsString('Found 2 clients', $response['response']);
        $this->assertStringContainsString('John Doe', $response['response']);
        $this->assertStringContainsString('Jane Smith', $response['response']);
        $this->assertTrue($response['metadata']['client_intent_detected']);
        $this->assertEquals('list', $response['metadata']['client_action']);
    }

    /** @test */
    public function it_handles_invalid_client_requests_gracefully()
    {
        $user = User::factory()->create();
        $chat = Chat::create([
            'user_id' => $user->id,
            'type' => 'clients',
            'title' => 'Client Management'
        ]);

        $message = "Show client nonexistent@example.com";

        $response = $this->aiChatService->processMessage($chat, $message);

        $this->assertTrue($response['success']);
        $this->assertStringContainsString('Client not found', $response['response']);
        $this->assertTrue($response['metadata']['client_intent_detected']);
        $this->assertEquals('read', $response['metadata']['client_action']);
    }

    /** @test */
    public function it_ignores_non_client_intents()
    {
        $user = User::factory()->create();
        $chat = Chat::create([
            'user_id' => $user->id,
            'type' => 'clients',
            'title' => 'Client Management'
        ]);

        $message = "Hello, how are you today?";

        $response = $this->aiChatService->processMessage($chat, $message);

        $this->assertTrue($response['success']);
        $this->assertFalse($response['metadata']['client_intent_detected']);
    }
}
