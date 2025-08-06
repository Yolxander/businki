# AI Chat Service Documentation

## Overview

The AI Chat Service is a comprehensive system that integrates AI/ML capabilities with context-aware services to provide intelligent responses based on chat type. The service analyzes user messages, builds context-aware prompts, and generates appropriate AI responses using the platform's available data and user context.

## Architecture

### Core Components

1. **AIChatService** - Main service that orchestrates AI chat functionality
2. **ContextAwareService** - Provides platform and user context
3. **OpenAIService** - Handles AI model interactions
4. **AIMLAPIService** - Alternative AI provider integration
5. **ChatController** - API endpoints for chat operations

### Service Dependencies

```php
AIChatService
├── OpenAIService (Primary AI provider)
├── ContextAwareService (Context management)
└── AIMLAPIService (Alternative AI provider)
```

## Features

### 1. Context-Aware Responses

The service analyzes user messages and builds comprehensive context including:
- User information and permissions
- Platform data availability
- Conversation history
- Chat type specific context
- Message intent analysis

### 2. Chat Type Specialization

Different chat types have specialized AI assistants:

- **General**: Business management queries
- **Projects**: Project management and tracking
- **Clients**: Client relationship management
- **Analytics**: Data analysis and reporting
- **Calendar**: Scheduling and time management
- **Bobbi-Flow**: Workflow automation
- **System**: Platform configuration

### 3. Message Analysis

The service performs intelligent message analysis:

- **Intent Detection**: Identifies user intent (data query, analysis request, action request, etc.)
- **Keyword Extraction**: Extracts relevant keywords for context building
- **Entity Recognition**: Identifies projects, clients, tasks, etc.
- **Context Specificity**: Determines if request is context-specific

### 4. AI Response Generation

- Uses OpenAI for primary responses
- Fallback to AIMLAPI for alternative providers
- Error handling with graceful degradation
- Response metadata tracking (tokens, cost, model used)

## API Endpoints

### Chat Management

```http
GET /api/chats/recent?type=general&limit=5
GET /api/chats/all?type=projects&page=1&per_page=10
GET /api/chats/{chatId}
POST /api/chats
POST /api/chats/{chatId}/messages
DELETE /api/chats/{chatId}
```

### Chat Features

```http
GET /api/chats/suggestions?type=analytics
GET /api/chats/stats
```

## Usage Examples

### Creating a New Chat

```javascript
const response = await fetch('/api/chats', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    },
    body: JSON.stringify({
        type: 'projects',
        first_message: 'Show me all active projects',
        title: 'Project Overview'
    })
});

const data = await response.json();
// data.data.chat contains the new chat information
```

### Sending a Message

```javascript
const response = await fetch(`/api/chats/${chatId}/messages`, {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    },
    body: JSON.stringify({
        content: 'What projects are due this week?',
        role: 'user',
        options: {
            temperature: 0.7,
            max_tokens: 2000
        }
    })
});

const data = await response.json();
// data.data.ai_response contains the AI response
```

### Getting Chat Suggestions

```javascript
const response = await fetch('/api/chats/suggestions?type=analytics');
const data = await response.json();
// data.data.suggestions contains suggested prompts
```

## Context Building Process

### 1. User Context
- User profile information
- Permissions and access levels
- User statistics and activity

### 2. Platform Context
- Available data tables
- Supported metrics
- Platform capabilities

### 3. Conversation Context
- Recent message history
- Chat type and purpose
- Message analysis results

### 4. AI Prompt Construction

The service builds comprehensive prompts:

```
System Prompt (Chat Type Specific)
├── User Context Information
├── Platform Context Information
├── Conversation History
└── User Message + Instructions
```

## Message Analysis Features

### Intent Detection

The service recognizes various user intents:

- **data_query**: "Show me", "List", "Find"
- **analysis_request**: "Analyze", "Report", "Summary"
- **action_request**: "Create", "Update", "Delete"
- **help_request**: "Help", "How to", "Guide"
- **status_check**: "Status", "Progress", "Current"

### Keyword Extraction

Automatically extracts relevant keywords:

```php
$keywordPatterns = [
    'clients' => ['client', 'customer', 'contact'],
    'projects' => ['project', 'work', 'job', 'assignment'],
    'tasks' => ['task', 'todo', 'work item', 'ticket'],
    'proposals' => ['proposal', 'quote', 'estimate'],
    'revenue' => ['revenue', 'income', 'money', 'earnings'],
    'analytics' => ['analytics', 'report', 'metrics', 'statistics'],
    'calendar' => ['calendar', 'schedule', 'meeting', 'appointment'],
    'bobbi_flow' => ['workflow', 'process', 'automation', 'flow']
];
```

### Entity Recognition

Extracts entities from user messages:
- Names (proper nouns)
- Numbers (dates, amounts)
- Context-specific terms

## Error Handling

### Graceful Degradation

The service includes comprehensive error handling:

1. **AI Service Failures**: Fallback responses when AI services are unavailable
2. **Context Building Errors**: Default context when context service fails
3. **Message Processing Errors**: Error responses with helpful information
4. **Validation Errors**: Clear error messages for invalid requests

### Logging

All interactions are logged for monitoring and debugging:

```php
Log::info('AI Chat Interaction', [
    'chat_id' => $chat->id,
    'user_id' => $chat->user_id,
    'chat_type' => $chat->type,
    'tokens_used' => $aiResponse['tokens'],
    'cost' => $aiResponse['cost'],
    'model_used' => $aiResponse['model']
]);
```

## Configuration

### Environment Variables

```env
OPENAI_API_KEY=your_openai_api_key
OPENAI_MODEL=gpt-4
OPENAI_MAX_TOKENS=2000
OPENAI_TEMPERATURE=0.7
```

### Service Configuration

The service can be configured through the service container:

```php
// In AppServiceProvider
$this->app->singleton(AIChatService::class, function ($app) {
    return new AIChatService(
        $app->make(OpenAIService::class),
        $app->make(ContextAwareService::class),
        $app->make(AIMLAPIService::class)
    );
});
```

## Performance Considerations

### Caching

- Context information is cached to reduce API calls
- User permissions are cached for session duration
- Platform context is refreshed periodically

### Token Management

- Response length is controlled to manage costs
- Token usage is tracked and logged
- Cost estimation is provided for monitoring

### Rate Limiting

- API endpoints include rate limiting
- AI service calls are throttled to prevent abuse
- User-specific limits are enforced

## Security

### Data Protection

- User data is isolated by user ID
- Sensitive information is not included in AI prompts
- API responses are sanitized

### Access Control

- All endpoints require authentication
- User can only access their own chats
- Permission checks are enforced

## Monitoring and Analytics

### Chat Statistics

The service provides comprehensive analytics:

```http
GET /api/chats/stats
```

Returns:
- Total chats and messages
- Chats by type distribution
- Recent activity
- Usage patterns

### Performance Metrics

- Response times
- Token usage
- Cost tracking
- Error rates

## Integration Examples

### Frontend Integration

```javascript
class AIChatManager {
    constructor() {
        this.currentChatId = null;
        this.chatType = 'general';
    }

    async createChat(type, firstMessage = null) {
        const response = await fetch('/api/chats', {
            method: 'POST',
            headers: this.getHeaders(),
            body: JSON.stringify({
                type: type,
                first_message: firstMessage
            })
        });
        
        const data = await response.json();
        this.currentChatId = data.data.chat.id;
        return data.data.chat;
    }

    async sendMessage(content) {
        if (!this.currentChatId) {
            throw new Error('No active chat');
        }

        const response = await fetch(`/api/chats/${this.currentChatId}/messages`, {
            method: 'POST',
            headers: this.getHeaders(),
            body: JSON.stringify({
                content: content,
                role: 'user'
            })
        });

        return await response.json();
    }

    getHeaders() {
        return {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        };
    }
}
```

### React Component Example

```jsx
import React, { useState, useEffect } from 'react';

const AIChatInterface = ({ chatType = 'general' }) => {
    const [messages, setMessages] = useState([]);
    const [input, setInput] = useState('');
    const [isLoading, setIsLoading] = useState(false);
    const [chatId, setChatId] = useState(null);

    const sendMessage = async () => {
        if (!input.trim()) return;

        setIsLoading(true);
        
        try {
            const response = await fetch(`/api/chats/${chatId}/messages`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    content: input,
                    role: 'user'
                })
            });

            const data = await response.json();
            
            setMessages(prev => [
                ...prev,
                data.data.message,
                data.data.ai_response
            ]);
            
            setInput('');
        } catch (error) {
            console.error('Error sending message:', error);
        } finally {
            setIsLoading(false);
        }
    };

    return (
        <div className="ai-chat-interface">
            <div className="messages">
                {messages.map((message, index) => (
                    <div key={index} className={`message ${message.role}`}>
                        {message.content}
                    </div>
                ))}
            </div>
            
            <div className="input-area">
                <input
                    type="text"
                    value={input}
                    onChange={(e) => setInput(e.target.value)}
                    onKeyPress={(e) => e.key === 'Enter' && sendMessage()}
                    placeholder="Type your message..."
                    disabled={isLoading}
                />
                <button onClick={sendMessage} disabled={isLoading}>
                    {isLoading ? 'Sending...' : 'Send'}
                </button>
            </div>
        </div>
    );
};
```

## Troubleshooting

### Common Issues

1. **AI Service Unavailable**
   - Check API keys and configuration
   - Verify network connectivity
   - Check service status

2. **Context Building Failures**
   - Ensure user authentication
   - Check database connectivity
   - Verify user permissions

3. **Response Quality Issues**
   - Adjust temperature and token settings
   - Review prompt construction
   - Check context relevance

### Debug Mode

Enable debug logging:

```php
// In config/logging.php
'channels' => [
    'ai_chat' => [
        'driver' => 'daily',
        'path' => storage_path('logs/ai-chat.log'),
        'level' => 'debug',
    ],
],
```

## Future Enhancements

### Planned Features

1. **Multi-modal Support**: Image and file uploads
2. **Voice Integration**: Speech-to-text and text-to-speech
3. **Advanced Analytics**: Sentiment analysis and trend detection
4. **Custom Models**: Fine-tuned models for specific use cases
5. **Real-time Streaming**: Streaming responses for better UX

### Performance Improvements

1. **Response Caching**: Cache common responses
2. **Async Processing**: Background message processing
3. **Load Balancing**: Multiple AI provider support
4. **CDN Integration**: Faster response delivery

## Support

For technical support or questions about the AI Chat Service:

1. Check the logs for error details
2. Review the API documentation
3. Test with the playground interface
4. Contact the development team

---

*Last updated: January 2025* 