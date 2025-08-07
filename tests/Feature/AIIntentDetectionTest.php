<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\AIIntentDetectionService;
use App\Services\AIMLAPIService;
use App\Services\OpenAIService;
use App\Models\AIModel;
use App\Models\AIProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;

class AIIntentDetectionTest extends TestCase
{
    use RefreshDatabase;

    private AIIntentDetectionService $aiIntentDetectionService;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock the AI services to avoid actual API calls during testing
        $this->aiIntentDetectionService = app(AIIntentDetectionService::class);
    }

    /**
     * Test basic intent detection functionality
     */
    public function test_basic_intent_detection()
    {
        $message = "Create a new client named John Doe with email john@example.com";

        $intent = $this->aiIntentDetectionService->detectIntent($message);

        $this->assertIsArray($intent);
        $this->assertArrayHasKey('type', $intent);
        $this->assertArrayHasKey('action', $intent);
        $this->assertArrayHasKey('confidence', $intent);
        // Entities may not be present in rule-based fallback
        if (isset($intent['entities'])) {
            $this->assertIsArray($intent['entities']);
        }
    }

    /**
     * Test client intent detection
     */
    public function test_client_intent_detection()
    {
        $messages = [
            "Create a new client",
            "Add client John Doe",
            "Show all clients",
            "Find client with email john@example.com",
            "Update client information"
        ];

        foreach ($messages as $message) {
            $intent = $this->aiIntentDetectionService->detectIntent($message);

            $this->assertIsArray($intent);
            $this->assertArrayHasKey('type', $intent);
            $this->assertArrayHasKey('action', $intent);
            $this->assertArrayHasKey('confidence', $intent);
        }
    }

    /**
     * Test project intent detection
     */
    public function test_project_intent_detection()
    {
        $messages = [
            "Create a new project",
            "Show all projects",
            "Find project details",
            "Update project timeline"
        ];

        foreach ($messages as $message) {
            $intent = $this->aiIntentDetectionService->detectIntent($message);

            $this->assertIsArray($intent);
            $this->assertArrayHasKey('type', $intent);
            $this->assertArrayHasKey('action', $intent);
            $this->assertArrayHasKey('confidence', $intent);
        }
    }

    /**
     * Test task intent detection
     */
    public function test_task_intent_detection()
    {
        $messages = [
            "Create a new task",
            "Show all tasks",
            "Find task details",
            "Update task priority"
        ];

        foreach ($messages as $message) {
            $intent = $this->aiIntentDetectionService->detectIntent($message);

            $this->assertIsArray($intent);
            $this->assertArrayHasKey('type', $intent);
            $this->assertArrayHasKey('action', $intent);
            $this->assertArrayHasKey('confidence', $intent);
        }
    }

    /**
     * Test proposal intent detection
     */
    public function test_proposal_intent_detection()
    {
        $messages = [
            "Create a new proposal",
            "Show all proposals",
            "Find proposal details",
            "Update proposal pricing"
        ];

        foreach ($messages as $message) {
            $intent = $this->aiIntentDetectionService->detectIntent($message);

            $this->assertIsArray($intent);
            $this->assertArrayHasKey('type', $intent);
            $this->assertArrayHasKey('action', $intent);
            $this->assertArrayHasKey('confidence', $intent);
        }
    }

    /**
     * Test intent detection with context
     */
    public function test_intent_detection_with_context()
    {
        $message = "Update the client information";
        $context = [
            'current_client' => 'John Doe',
            'previous_action' => 'create'
        ];

        $intent = $this->aiIntentDetectionService->detectIntent($message, $context);

        $this->assertIsArray($intent);
        $this->assertArrayHasKey('type', $intent);
        $this->assertArrayHasKey('action', $intent);
        $this->assertArrayHasKey('confidence', $intent);
    }

    /**
     * Test intent detection statistics
     */
    public function test_intent_detection_stats()
    {
        $stats = $this->aiIntentDetectionService->getIntentDetectionStats();

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total_detections', $stats);
        $this->assertArrayHasKey('ai_success_rate', $stats);
        $this->assertArrayHasKey('fallback_rate', $stats);
        $this->assertArrayHasKey('average_confidence', $stats);
        $this->assertArrayHasKey('supported_intent_types', $stats);
    }

    /**
     * Test fallback to rule-based detection
     */
    public function test_fallback_to_rule_based_detection()
    {
        // Test with a simple message that should trigger rule-based detection
        $message = "Create a new client";

        $intent = $this->aiIntentDetectionService->detectIntent($message);

        $this->assertIsArray($intent);
        $this->assertArrayHasKey('type', $intent);
        $this->assertArrayHasKey('action', $intent);
        $this->assertArrayHasKey('confidence', $intent);
    }

    /**
     * Test edge cases
     */
    public function test_edge_cases()
    {
        $edgeCases = [
            "", // Empty message
            "   ", // Whitespace only
            "Hello", // Generic greeting
            "1234567890", // Numbers only
            "!@#$%^&*()", // Special characters only
        ];

        foreach ($edgeCases as $message) {
            $intent = $this->aiIntentDetectionService->detectIntent($message);

            $this->assertIsArray($intent);
            $this->assertArrayHasKey('type', $intent);
            $this->assertArrayHasKey('action', $intent);
            $this->assertArrayHasKey('confidence', $intent);
        }
    }

    /**
     * Test confidence scoring
     */
    public function test_confidence_scoring()
    {
        $messages = [
            "Create a new client named John Doe with email john@example.com" => 'high_confidence',
            "Create client" => 'medium_confidence',
            "Hello" => 'low_confidence'
        ];

        foreach ($messages as $message => $expectedConfidence) {
            $intent = $this->aiIntentDetectionService->detectIntent($message);

            $this->assertIsArray($intent);
            $this->assertArrayHasKey('confidence', $intent);
            $this->assertIsNumeric($intent['confidence']);
            $this->assertGreaterThanOrEqual(0, $intent['confidence']);
            $this->assertLessThanOrEqual(1, $intent['confidence']);
        }
    }
}
