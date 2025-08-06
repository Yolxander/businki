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

echo "=== Interactive Client Creation Demo (Fixed) ===\n\n";

// Create a test user and chat
$user = User::factory()->create();
$chat = Chat::create([
    'user_id' => $user->id,
    'type' => 'clients',
    'title' => 'Interactive Client Demo'
]);

$aiChatService = app(AIChatService::class);

// Demo: Interactive client creation with sequential field collection
echo "Demo: Sequential Field Collection (Fixed)\n\n";

// Step 1: User tries to create client with incomplete information
echo "Step 1: User provides incomplete information\n";
echo "User: Create a new client named Juan\n";

$response = $aiChatService->processMessage($chat, "Create a new client named Juan");
echo "AI: " . $response['response'] . "\n\n";

// Step 2: User provides email
echo "Step 2: User provides email\n";
echo "User: juan@example.com\n";

$response = $aiChatService->processMessage($chat, "juan@example.com");
echo "AI: " . $response['response'] . "\n\n";

// Step 3: User provides last name
echo "Step 3: User provides last name\n";
echo "User: Garcia\n";

$response = $aiChatService->processMessage($chat, "Garcia");
echo "AI: " . $response['response'] . "\n\n";

// Demo 2: Another interactive flow
echo "\nDemo 2: Another Interactive Flow\n\n";

// Step 1: User provides only email
echo "Step 1: User provides only email\n";
echo "User: Create client with email maria@test.com\n";

$response = $aiChatService->processMessage($chat, "Create client with email maria@test.com");
echo "AI: " . $response['response'] . "\n\n";

// Step 2: User provides first name
echo "Step 2: User provides first name\n";
echo "User: Maria\n";

$response = $aiChatService->processMessage($chat, "Maria");
echo "AI: " . $response['response'] . "\n\n";

// Step 3: User provides last name
echo "Step 3: User provides last name\n";
echo "User: Rodriguez\n";

$response = $aiChatService->processMessage($chat, "Rodriguez");
echo "AI: " . $response['response'] . "\n\n";

echo "Demo completed!\n";
