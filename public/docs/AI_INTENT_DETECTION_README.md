# AI Intent Detection Service

## Overview

The AI Intent Detection Service is a sophisticated system that combines AI-powered intent detection with rule-based fallbacks to accurately identify user intents from business management conversations. The service supports multiple intent types and provides high-confidence detection using various AI models.

## Architecture

### Core Components

1. **AIIntentDetectionService** - Main service that orchestrates AI and rule-based intent detection
2. **AIMLAPIService** - Enhanced with intent detection capabilities
3. **OpenAIService** - Enhanced with intent detection capabilities
4. **IntentDetectionService** - Rule-based fallback service
5. **IntentDetectionController** - API endpoints for intent detection

### Service Dependencies

```php
AIIntentDetectionService
├── AIMLAPIService (AI-powered detection for all providers)
└── IntentDetectionService (Rule-based fallback)
```

## Features

### 1. Multi-Model AI Detection

The service supports multiple AI providers and models through the AIMLAPI service:

- **OpenAI Models**: GPT-4o, GPT-4o-mini, GPT-4-turbo, GPT-3.5-turbo
- **Anthropic Models**: Claude-3-opus, Claude-3-sonnet, Claude-3-haiku
- **Google Models**: Gemini-pro, Gemini-pro-vision
- **Database Models**: User-configured models from the AI models table

### 2. Supported Intent Types

The service detects the following intent types:

- **client**: Client management (create, read, update, delete, list)
- **project**: Project management (create, read, update, delete, list)
- **task**: Task management (create, read, update, delete, list)
- **proposal**: Proposal management (create, read, update, delete, list)
- **analytics**: Data analysis and reporting
- **calendar**: Scheduling and time management
- **general**: General business queries
- **system**: System configuration and settings

### 3. Supported Actions

Each intent type supports various actions:

- **create**: Create new item
- **read**: View or get information
- **update**: Modify existing item
- **delete**: Remove item
- **list**: Show multiple items
- **analyze**: Analyze data or generate reports
- **schedule**: Schedule or plan
- **configure**: Configure settings

### 4. Entity Extraction

The service extracts various entities from user messages:

- **name**: Person or company name
- **email**: Email address
- **phone**: Phone number
- **company**: Company name
- **project_id**: Project identifier
- **task_id**: Task identifier
- **client_id**: Client identifier
- **date**: Date or time
- **status**: Status information
- **priority**: Priority level
- **amount**: Monetary amount
- **description**: Description or details

## Configuration

### Environment Variables

The service uses existing AI configuration from your `.env` file:

```env
# OpenAI Configuration
OPENAI_API_KEY=your_openai_api_key
OPENAI_MODEL=gpt-4o-mini
OPENAI_MAX_TOKENS=4000
OPENAI_TEMPERATURE=0.7

# AIMLAPI Configuration
AIMLAPI_API_KEY=your_aimlapi_api_key
AIMLAPI_BASE_URL=https://api.aimlapi.com/v1
AIMLAPI_MODEL=gpt-4o
AIMLAPI_MAX_TOKENS=2048
AIMLAPI_TEMPERATURE=0.7
```

### Service Configuration

The services are configured in `config/services.php`:

```php
'openai' => [
    'api_key' => env('OPENAI_API_KEY'),
    'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
    'max_tokens' => env('OPENAI_MAX_TOKENS', 4000),
    'temperature' => env('OPENAI_TEMPERATURE', 0.7),
],

'aimlapi' => [
    'api_key' => env('AIMLAPI_API_KEY'),
    'base_url' => env('AIMLAPI_BASE_URL', 'https://api.aimlapi.com/v1'),
    'model' => env('AIMLAPI_MODEL', 'gpt-4o'),
    'max_tokens' => env('AIMLAPI_MAX_TOKENS', 2048),
    'temperature' => env('AIMLAPI_TEMPERATURE', 0.7),
],
```

## API Endpoints

### 1. Detect Intent

**Endpoint:** `POST /api/intent-detection/detect`

**Request Body:**
```json
{
    "message": "Create a new client named John Doe",
    "context": {
        "current_user": "user_id",
        "previous_action": "create"
    },
    "ai_model_id": 1,
    "use_ai": true
}
```

**Response:**
```json
{
    "status": "success",
    "message": "Intent detected successfully",
    "data": {
        "type": "client",
        "action": "create",
        "confidence": 0.85,
        "entities": {
            "name": "John Doe"
        },
        "original_message": "Create a new client named John Doe"
    }
}
```

### 2. Test Intent Detection

**Endpoint:** `POST /api/intent-detection/test`

**Request Body:**
```json
{
    "message": "Show all projects",
    "context": {},
    "model": "gpt-4o",
    "provider": "aimlapi"
}
```

**Response:**
```json
{
    "status": "success",
    "message": "Intent detection test completed",
    "data": {
        "intent": {
            "type": "project",
            "action": "list",
            "confidence": 0.92,
            "entities": {}
        },
        "raw_response": "AI model response...",
        "tokens": 150,
        "cost": "$0.0030",
        "response_time_ms": 1250.5,
        "model": "gpt-4o",
        "provider": "aimlapi"
    }
}
```

### 3. Get Available Models

**Endpoint:** `GET /api/intent-detection/models`

**Response:**
```json
{
    "status": "success",
    "message": "Available models retrieved successfully",
    "data": {
        "aimlapi_models": {
            "gpt-4o": "GPT-4 Omni (Best for intent detection)",
            "claude-3-opus": "Claude 3 Opus (High accuracy)",
            "gemini-pro": "Gemini Pro (Good for intent detection)"
        },
        "database_models": [
            {
                "id": 1,
                "name": "Default Model",
                "model": "gpt-4o",
                "provider": "aimlapi",
                "is_default": true
            }
        ]
    }
}
```

### 4. Get Statistics

**Endpoint:** `GET /api/intent-detection/stats`

**Response:**
```json
{
    "status": "success",
    "message": "Statistics retrieved successfully",
    "data": {
        "total_detections": 1250,
        "ai_success_rate": 0.85,
        "fallback_rate": 0.15,
        "average_confidence": 0.75,
        "supported_intent_types": [
            "client", "project", "task", "proposal",
            "analytics", "calendar", "general", "system"
        ]
    }
}
```

### 5. Batch Intent Detection

**Endpoint:** `POST /api/intent-detection/batch`

**Request Body:**
```json
{
    "messages": [
        "Create a new client",
        "Show all projects",
        "Update task priority"
    ],
    "context": {},
    "ai_model_id": 1
}
```

**Response:**
```json
{
    "status": "success",
    "message": "Batch intent detection completed",
    "data": {
        "results": [
            {
                "index": 0,
                "message": "Create a new client",
                "intent": {
                    "type": "client",
                    "action": "create",
                    "confidence": 0.85
                },
                "response_time_ms": 1200.5,
                "status": "success"
            }
        ],
        "total_messages": 3,
        "successful_detections": 3,
        "failed_detections": 0,
        "total_time_ms": 3500.2,
        "average_time_ms": 1166.7
    }
}
```

## Usage Examples

### Basic Intent Detection

```php
use App\Services\AIIntentDetectionService;

$aiIntentDetectionService = app(AIIntentDetectionService::class);

$message = "Create a new client named John Doe with email john@example.com";
$intent = $aiIntentDetectionService->detectIntent($message);

// Result:
// [
//     'type' => 'client',
//     'action' => 'create',
//     'confidence' => 0.85,
//     'entities' => [
//         'name' => 'John Doe',
//         'email' => 'john@example.com'
//     ]
// ]
```

### Intent Detection with Context

```php
$context = [
    'current_user' => 'user_123',
    'previous_action' => 'create',
    'current_client' => 'John Doe'
];

$intent = $aiIntentDetectionService->detectIntent($message, $context);
```

### Using Specific AI Model

```php
use App\Models\AIModel;

$aiModel = AIModel::find(1);
$intent = $aiIntentDetectionService->detectIntent($message, [], $aiModel);
```

## Integration with Chat Service

The AI Intent Detection Service is integrated with the AIChatService to provide intelligent responses:

```php
// In AIChatService::processMessage()
$aiIntent = $this->aiIntentDetectionService->detectIntent($message, $context);

if ($aiIntent['confidence'] >= 0.7) {
    if ($aiIntent['type'] === 'client') {
        return $this->handleClientIntent($chat, $message, $aiIntent, $context);
    } elseif ($aiIntent['type'] === 'project') {
        return $this->handleProjectIntent($chat, $message, $aiIntent, $context);
    }
    // ... handle other intent types
}
```

## Performance Optimization

### 1. Caching

The service uses caching for:
- Intent detection statistics
- Model configurations
- Frequently used prompts

### 2. Fallback Strategy

The service implements a smart fallback strategy:
1. Try AI-powered detection first
2. If confidence < 0.7, fall back to rule-based detection
3. If AI detection fails, use rule-based detection

### 3. Batch Processing

For multiple messages, use the batch endpoint to reduce API calls and improve performance.

## Error Handling

The service includes comprehensive error handling:

```php
try {
    $intent = $aiIntentDetectionService->detectIntent($message);
} catch (Exception $e) {
    // Fallback to rule-based detection
    $intent = $this->intentDetectionService->detectClientIntent($message);
}
```

## Testing

Run the intent detection tests:

```bash
php artisan test tests/Feature/AIIntentDetectionTest.php
```

## Best Practices

### 1. Model Selection

- Use GPT-4o for highest accuracy
- Use GPT-4o-mini for faster responses
- Use Claude-3-opus for complex intent detection

### 2. Context Usage

Always provide relevant context for better intent detection:

```php
$context = [
    'current_user' => $user->id,
    'chat_type' => $chat->type,
    'previous_messages' => $recentMessages,
    'user_permissions' => $userPermissions
];
```

### 3. Confidence Thresholds

- High confidence (≥0.8): Use AI detection result
- Medium confidence (0.5-0.8): Consider fallback
- Low confidence (<0.5): Use rule-based detection

### 4. Entity Validation

Always validate extracted entities:

```php
if (isset($intent['entities']['email'])) {
    $email = filter_var($intent['entities']['email'], FILTER_VALIDATE_EMAIL);
    if (!$email) {
        // Handle invalid email
    }
}
```

## Troubleshooting

### Common Issues

1. **Low Confidence Scores**
   - Check if the message contains clear intent keywords
   - Provide more context
   - Try a different AI model

2. **API Errors**
   - Verify API keys are configured correctly
   - Check API rate limits
   - Ensure network connectivity

3. **Incorrect Intent Detection**
   - Review the training data
   - Adjust confidence thresholds
   - Use more specific prompts

### Debugging

Enable debug logging:

```php
Log::debug('Intent detection', [
    'message' => $message,
    'context' => $context,
    'result' => $intent
]);
```

## Future Enhancements

1. **Custom Intent Types**: Allow users to define custom intent types
2. **Multi-language Support**: Support for multiple languages
3. **Intent Training**: Machine learning to improve detection accuracy
4. **Real-time Learning**: Learn from user corrections
5. **Intent Analytics**: Detailed analytics and insights

## Support

For issues or questions about the AI Intent Detection Service:

1. Check the logs for error messages
2. Review the API documentation
3. Test with the provided endpoints
4. Contact the development team

---

*This documentation is maintained by the Businki development team.* 
