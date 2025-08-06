<?php

require_once 'vendor/autoload.php';

use App\Models\User;
use App\Models\Chat;
use App\Models\Client;
use App\Services\AIChatService;
use App\Services\IntentDetectionService;

// Initialize Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Client CRUD via AI Chat Demo ===\n\n";

// Create a test user and chat
$user = User::factory()->create();
$chat = Chat::create([
    'user_id' => $user->id,
    'type' => 'clients',
    'title' => 'Client Management Demo'
]);

$aiChatService = app(AIChatService::class);
$intentService = app(IntentDetectionService::class);

// Demo 1: Create a client
echo "Demo 1: Creating a client\n";
echo "User: Create a new client named Sarah Johnson with email sarah@techcorp.com and phone 555-1234\n";

$response = $aiChatService->processMessage($chat, "Create a new client named Sarah Johnson with email sarah@techcorp.com and phone 555-1234");

echo "AI: " . $response['response'] . "\n\n";

// Demo 2: List all clients
echo "Demo 2: Listing all clients\n";
echo "User: List all clients\n";

$response = $aiChatService->processMessage($chat, "List all clients");

echo "AI: " . $response['response'] . "\n\n";

// Demo 3: Read a specific client
echo "Demo 3: Reading a specific client\n";
echo "User: Show client sarah@techcorp.com\n";

$response = $aiChatService->processMessage($chat, "Show client sarah@techcorp.com");

echo "AI: " . $response['response'] . "\n\n";

// Demo 4: Try to read non-existent client
echo "Demo 4: Reading non-existent client\n";
echo "User: Show client nonexistent@example.com\n";

$response = $aiChatService->processMessage($chat, "Show client nonexistent@example.com");

echo "AI: " . $response['response'] . "\n\n";

// Demo 5: Regular chat message (not client CRUD)
echo "Demo 5: Regular chat message\n";
echo "User: Hello, how are you today?\n";

$response = $aiChatService->processMessage($chat, "Hello, how are you today?");

echo "AI: " . $response['response'] . "\n\n";

echo "=== Demo Complete ===\n";
echo "This demonstrates how the AI chat system can:\n";
echo "- Detect client-related intents from natural language\n";
echo "- Extract structured data from conversational input\n";
echo "- Perform CRUD operations on client records\n";
echo "- Provide natural language responses\n";
echo "- Handle errors gracefully\n";
echo "- Distinguish between CRUD commands and regular chat\n";
