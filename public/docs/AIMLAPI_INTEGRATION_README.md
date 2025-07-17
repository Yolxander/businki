# AIMLAPI Integration

This document describes the AIMLAPI integration in the Businki application.

## Overview

The AIMLAPI integration provides access to multiple AI models through a single API endpoint, similar to OpenAI's API but with support for various providers including GPT-4, Claude-3, and Gemini Pro.

## Configuration

### Environment Variables

Add the following environment variables to your `.env` file:

```env
AIMLAPI_API_KEY=your_aimlapi_api_key_here
AIMLAPI_BASE_URL=https://api.aimlapi.com/v1
AIMLAPI_MODEL=gpt-4o
AIMLAPI_MAX_TOKENS=2048
AIMLAPI_TEMPERATURE=0.7
AIMLAPI_TOP_P=0.9
AIMLAPI_FREQUENCY_PENALTY=0.0
AIMLAPI_PRESENCE_PENALTY=0.0
```

### Service Configuration

The AIMLAPI service is configured in `config/services.php`:

```php
'aimlapi' => [
    'api_key' => env('AIMLAPI_API_KEY'),
    'base_url' => env('AIMLAPI_BASE_URL', 'https://api.aimlapi.com/v1'),
    'model' => env('AIMLAPI_MODEL', 'gpt-4o'),
    'max_tokens' => env('AIMLAPI_MAX_TOKENS', 2048),
    'temperature' => env('AIMLAPI_TEMPERATURE', 0.7),
    'top_p' => env('AIMLAPI_TOP_P', 0.9),
    'frequency_penalty' => env('AIMLAPI_FREQUENCY_PENALTY', 0.0),
    'presence_penalty' => env('AIMLAPI_PRESENCE_PENALTY', 0.0),
],
```

## Services

### AIMLAPIService

The main service class is located at `app/Services/AIMLAPIService.php`. It provides the following methods:

#### Core Methods

- `testConnection()` - Test the AIMLAPI connection
- `generateChatCompletion(string $message, array $options = [])` - Generate a simple chat completion
- `generateProposal(array $intakeData, array $options = [])` - Generate a proposal from intake data
- `generateProject(array $proposalData, array $options = [])` - Generate a project from proposal data
- `generateTasks(array $projectData, array $options = [])` - Generate tasks for a project

#### Configuration Methods

- `getConfiguration()` - Get current service configuration
- `updateConfiguration(array $config)` - Update service configuration

#### Prompt Building Methods

- `buildProposalPrompt(array $intakeData, bool $includeDeliverables, bool $includeTimeline, bool $includePricing)` - Build proposal generation prompt
- `buildProjectPrompt(array $proposalData, bool $includeTimeline, bool $includePhases)` - Build project generation prompt
- `buildTasksPrompt(array $projectData, int $maxTasks, bool $includeSubtasks)` - Build tasks generation prompt

## API Endpoints

### AI Settings Endpoints

All AI settings endpoints are prefixed with `/api/ai-settings` and require authentication:

- `POST /api/ai-settings/test-aimlapi-connection` - Test AIMLAPI connection
- `POST /api/ai-settings/test-openai-connection` - Test OpenAI connection
- `GET /api/ai-settings/models` - Get AI models configuration
- `GET /api/ai-settings/configuration` - Get AI configuration parameters
- `PUT /api/ai-settings/configuration` - Update AI configuration
- `POST /api/ai-settings/test-completion` - Generate test chat completion
- `GET /api/ai-settings/stats` - Get AI settings statistics
- `PATCH /api/ai-settings/models/{modelId}/status` - Toggle model status

### Example Usage

#### Test Connection

```javascript
const response = await fetch('/api/ai-settings/test-aimlapi-connection', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    }
});

const data = await response.json();
console.log(data);
```

#### Update Configuration

```javascript
const configData = {
    temperature: 0.8,
    max_tokens: 1000,
    top_p: 0.95
};

const response = await fetch('/api/ai-settings/configuration', {
    method: 'PUT',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    },
    body: JSON.stringify(configData)
});

const data = await response.json();
console.log(data);
```

#### Test Completion

```javascript
const testData = {
    message: 'Hello, how are you?',
    model: 'gpt-4o',
    temperature: 0.7
};

const response = await fetch('/api/ai-settings/test-completion', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    },
    body: JSON.stringify(testData)
});

const data = await response.json();
console.log(data.content);
```

## Frontend Integration

### AI Settings Page

The AI settings page (`/ai-settings`) provides a comprehensive interface for managing AI configurations:

- **Stats Cards**: Display active models, total requests, costs, and success rates
- **AI Models**: List and manage AI model integrations
- **Model Parameters**: Configure temperature, max tokens, top P, frequency penalty, and presence penalty
- **Security & Monitoring**: API key management and usage monitoring

### Model Details Page

The model details page (`/ai-settings/models/{modelId}`) provides detailed configuration for individual models:

- **Model Information**: Display model details, status, and usage statistics
- **Model Parameters**: Fine-tune AI response behavior with interactive sliders
- **Test AI Completion**: Test the model with custom messages
- **Performance Metrics**: View response times, success rates, token usage, and uptime

## Error Handling

The service includes comprehensive error handling:

- Connection failures are logged and return appropriate error messages
- API rate limits are handled gracefully
- Invalid configurations are validated before sending requests
- All errors are logged for debugging purposes

## Testing

Unit tests are available in `tests/Unit/AIMLAPIServiceTest.php`:

```bash
php artisan test tests/Unit/AIMLAPIServiceTest.php
```

## Security Considerations

- API keys are stored securely in environment variables
- All API requests include proper authentication headers
- Rate limiting is implemented to prevent abuse
- Input validation is performed on all user inputs
- Error messages don't expose sensitive information

## Troubleshooting

### Common Issues

1. **Connection Failed**: Check your API key and network connection
2. **Rate Limited**: Reduce request frequency or upgrade your plan
3. **Invalid Configuration**: Verify all configuration parameters are within valid ranges
4. **Model Not Available**: Ensure the requested model is available in your AIMLAPI plan

### Debug Mode

Enable debug logging by setting `APP_DEBUG=true` in your `.env` file to see detailed error messages.

## Support

For issues with the AIMLAPI integration:

1. Check the Laravel logs in `storage/logs/laravel.log`
2. Verify your AIMLAPI account and API key
3. Test the connection using the test endpoint
4. Review the AIMLAPI documentation for API-specific issues 
