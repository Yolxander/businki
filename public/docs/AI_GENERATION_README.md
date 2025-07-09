# AI Generation Service for Laravel

This document describes the AI generation service implementation for the Businki project, which provides automated content generation for proposals, projects, and tasks using OpenAI's API.

## Overview

The AI generation service consists of:

1. **OpenAIService** - Core service class that handles all AI generation requests
2. **AIGenerationController** - API controller that handles the generation endpoints
3. **Updated Models** - Enhanced relationships between models
4. **API Routes** - New endpoints for AI generation

## Configuration

### Environment Variables

Add the following to your `.env` file:

```env
OPENAI_API_KEY=your_openai_api_key_here
OPENAI_MODEL=gpt-4o-mini
OPENAI_MAX_TOKENS=4000
OPENAI_TEMPERATURE=0.7
```

### Services Configuration

The OpenAI configuration is already added to `config/services.php`:

```php
'openai' => [
    'api_key' => env('OPENAI_API_KEY'),
    'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
    'max_tokens' => env('OPENAI_MAX_TOKENS', 4000),
    'temperature' => env('OPENAI_TEMPERATURE', 0.7),
],
```

## API Endpoints

### 1. Generate Proposal from Intake Response

**Endpoint:** `POST /api/intake-responses/{id}/generate-proposal`

**Request Body:**
```json
{
    "include_deliverables": true,
    "include_timeline": true,
    "include_pricing": true
}
```

**Response:**
```json
{
    "status": "success",
    "message": "Proposal generated successfully",
    "data": {
        "id": 1,
        "intake_response_id": 1,
        "user_id": 1,
        "title": "Professional Website Development Proposal",
        "scope": "Detailed project scope...",
        "deliverables": ["Responsive Website", "SEO Optimization"],
        "timeline": [
            {
                "id": "phase1",
                "description": "Design Phase",
                "duration": "2 weeks",
                "price": 1500
            }
        ],
        "price": 5000,
        "status": "draft"
    }
}
```

### 2. Generate Project from Proposal

**Endpoint:** `POST /api/proposals/{id}/generate-project`

**Request Body:**
```json
{
    "proposal_data": {
        "title": "Project Title",
        "scope": "Project scope...",
        "deliverables": ["Deliverable 1", "Deliverable 2"],
        "timeline": [...],
        "price": 5000
    },
    "include_timeline": true,
    "include_phases": true
}
```

**Response:**
```json
{
    "status": "success",
    "message": "Project generated successfully",
    "data": {
        "id": 1,
        "proposal_id": 1,
        "title": "Website Development Project",
        "status": "not_started",
        "current_phase": "Planning",
        "kickoff_date": "2024-01-15",
        "expected_delivery": "2024-03-15",
        "notes": "Project notes..."
    }
}
```

### 3. Generate Personal AI Project

**Endpoint:** `POST /api/projects/generate-personal-ai-project`

**Request Body:**
```json
{
    "project_type": "Web Development",
    "description": "Create a portfolio website",
    "include_in_portfolio": true
}
```

**Response:**
```json
{
    "status": "success",
    "message": "Personal project generated successfully",
    "data": {
        "id": 1,
        "proposal_id": null,
        "title": "Portfolio Website Development",
        "status": "not_started",
        "current_phase": "Planning",
        "kickoff_date": "2024-01-15",
        "expected_delivery": "2024-02-15",
        "notes": "Personal portfolio project..."
    }
}
```

### 4. Generate Tasks for Project

**Endpoint:** `POST /api/projects/{id}/generate-tasks`

**Request Body:**
```json
{
    "project_description": "Website development project",
    "project_scope": "Create a responsive website with CMS",
    "timeline": [
        {
            "id": "phase1",
            "description": "Design Phase",
            "duration": "2 weeks"
        }
    ],
    "max_tasks": 10,
    "include_subtasks": true
}
```

**Response:**
```json
{
    "status": "success",
    "message": "Tasks generated successfully",
    "data": [
        {
            "id": 1,
            "project_id": 1,
            "title": "Design Wireframes",
            "description": "Create wireframes for all pages",
            "status": "todo",
            "priority": "high",
            "estimated_hours": 8,
            "tags": ["design", "wireframes"],
            "subtasks": [
                {
                    "id": 1,
                    "task_id": 1,
                    "description": "Homepage wireframe",
                    "status": "todo"
                }
            ]
        }
    ]
}
```

### 5. Generate Personal Project Tasks

**Endpoint:** `POST /api/projects/generate-personal-tasks`

**Request Body:**
```json
{
    "project_type": "Web Development",
    "project_title": "Portfolio Website",
    "description": "Create a personal portfolio website"
}
```

**Response:**
```json
{
    "status": "success",
    "message": "Personal tasks generated successfully",
    "data": {
        "tasks": [
            {
                "title": "Design Homepage",
                "description": "Create homepage design",
                "priority": "high",
                "estimated_hours": 6,
                "tags": ["design", "homepage"]
            }
        ]
    }
}
```

## Service Usage

### Injecting the Service

The `OpenAIService` can be injected into any controller or service:

```php
use App\Services\OpenAIService;

class MyController extends Controller
{
    public function __construct(private OpenAIService $openAIService)
    {
    }

    public function someMethod()
    {
        $proposal = $this->openAIService->generateProposal($intakeData, $options);
    }
}
```

### Direct Service Usage

```php
$openAIService = app(OpenAIService::class);

// Generate proposal
$proposal = $openAIService->generateProposal($intakeData, [
    'include_deliverables' => true,
    'include_timeline' => true,
    'include_pricing' => true
]);

// Generate project
$project = $openAIService->generateProject($proposalData, [
    'include_timeline' => true,
    'include_phases' => true
]);

// Generate tasks
$tasks = $openAIService->generateTasks($projectData, [
    'max_tasks' => 10,
    'include_subtasks' => true
]);
```

## Error Handling

The service includes comprehensive error handling:

- **API Errors**: Logged and thrown as exceptions
- **Validation Errors**: Returned with proper HTTP status codes
- **Database Errors**: Wrapped in transactions for data integrity
- **Rate Limiting**: Built into the OpenAI API calls

### Common Error Responses

```json
{
    "status": "error",
    "message": "Failed to generate proposal: OpenAI API request failed: 401"
}
```

```json
{
    "status": "error",
    "message": "Validation failed",
    "errors": {
        "project_type": ["The project type field is required."]
    }
}
```

## Database Relationships

The following relationships have been added/updated:

### IntakeResponse Model
```php
public function proposal()
{
    return $this->hasOne(Proposal::class);
}
```

### Proposal Model
```php
public function project()
{
    return $this->hasOne(Project::class);
}
```

### Intake Model
```php
public function responses()
{
    return $this->hasMany(IntakeResponse::class);
}
```

## Testing

### Unit Tests
Run the unit tests for the OpenAI service:

```bash
php artisan test tests/Unit/OpenAIServiceTest.php
```

### Feature Tests
Run the feature tests for the AI generation endpoints:

```bash
php artisan test tests/Feature/AIGenerationTest.php
```

## Security Considerations

1. **API Key Protection**: OpenAI API key is stored in environment variables
2. **Authentication**: All endpoints require authentication
3. **Input Validation**: All inputs are validated before processing
4. **Rate Limiting**: Consider implementing rate limiting for production
5. **Error Logging**: Sensitive information is not logged

## Production Deployment

1. **Set Environment Variables**: Ensure all OpenAI configuration is set
2. **Monitor API Usage**: Track OpenAI API usage and costs
3. **Error Monitoring**: Set up error monitoring for AI generation failures
4. **Rate Limiting**: Implement rate limiting to prevent abuse
5. **Caching**: Consider caching AI responses for similar requests

## Troubleshooting

### Common Issues

1. **OpenAI API Key Not Set**
   - Ensure `OPENAI_API_KEY` is set in your `.env` file

2. **Model Not Found Errors**
   - Check that all required models exist and relationships are correct

3. **Validation Errors**
   - Ensure all required fields are provided in the request

4. **Database Transaction Errors**
   - Check database connection and table structure

### Debug Mode

Enable debug mode to see detailed error messages:

```php
// In config/app.php
'debug' => env('APP_DEBUG', true),
```

## Support

For issues or questions about the AI generation service:

1. Check the Laravel logs in `storage/logs/laravel.log`
2. Verify OpenAI API key and configuration
3. Test with the provided unit and feature tests
4. Review the error responses for specific issues 
