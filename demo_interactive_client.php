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

echo "=== Interactive Client Creation Demo ===\n\n";

// Create a test user and chat
$user = User::factory()->create();
$chat = Chat::create([
    'user_id' => $user->id,
    'type' => 'clients',
    'title' => 'Interactive Client Demo'
]);

$aiChatService = app(AIChatService::class);

// Demo: Interactive client creation with missing fields
echo "Demo: Interactive Client Creation\n\n";

// Step 1: User tries to create client with missing information
echo "Step 1: User provides incomplete information\n";
echo "User: Create a new client named John\n";

$response = $aiChatService->processMessage($chat, "Create a new client named John");
echo "AI: " . $response['response'] . "\n\n";

// Step 2: User provides email
echo "Step 2: User provides email\n";
echo "User: john@example.com\n";

$response = $aiChatService->processMessage($chat, "john@example.com");
echo "AI: " . $response['response'] . "\n\n";

// Step 3: User provides last name
echo "Step 3: User provides last name\n";
echo "User: Smith\n";

$response = $aiChatService->processMessage($chat, "Smith");
echo "AI: " . $response['response'] . "\n\n";

// Demo 2: Another interactive flow
echo "\nDemo 2: Another Interactive Flow\n\n";

// Step 1: User provides only email
echo "Step 1: User provides only email\n";
echo "User: Create client with email jane@test.com\n";

$response = $aiChatService->processMessage($chat, "Create client with email jane@test.com");
echo "AI: " . $response['response'] . "\n\n";

// Step 2: User provides name
echo "Step 2: User provides name\n";
echo "User: Jane Doe\n";

$response = $aiChatService->processMessage($chat, "Jane Doe");
echo "AI: " . $response['response'] . "\n\n";

// Demo 3: Complete information in one go
echo "\nDemo 3: Complete Information\n";
echo "User: Create a new client named Bob Wilson with email bob@company.com and phone 555-9876\n";

$response = $aiChatService->processMessage($chat, "Create a new client named Bob Wilson with email bob@company.com and phone 555-9876");
echo "AI: " . $response['response'] . "\n\n";

echo "=== Demo Complete ===\n";
echo "This demonstrates how the AI can:\n";
echo "- Ask for missing information naturally\n";
echo "- Handle follow-up messages with additional data\n";
echo "- Complete client creation when all required fields are provided\n";
echo "- Provide conversational responses instead of technical errors\n"; 