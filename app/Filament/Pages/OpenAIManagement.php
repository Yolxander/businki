<?php

namespace App\Filament\Pages;

use App\Models\AIGenerationSetting;
use App\Services\OpenAIService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Card;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class OpenAIManagement extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $view = 'filament.pages.openai-management';

    protected static ?string $title = 'OpenAI API Management';

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'OpenAI Management';

    protected static ?string $navigationGroup = 'AI Generation';

    protected static ?string $slug = 'openai-management';

    protected static ?int $navigationSort = 2;

    // Form properties
    public ?string $apiKey = null;
    public ?string $model = 'gpt-4o-mini';
    public ?float $temperature = 0.7;
    public ?int $maxTokens = 4000;
    public ?float $topP = 1.0;
    public ?float $frequencyPenalty = 0.0;
    public ?float $presencePenalty = 0.0;
    public ?string $systemPrompt = null;
    public ?string $userPrompt = null;
    public ?string $testType = 'chat';
    public ?array $functionCall = null;
    public ?bool $streamResponse = false;

    // Response properties
    public ?string $apiResponse = null;
    public ?array $responseMetadata = null;
    public ?string $errorMessage = null;
    public ?bool $isLoading = false;

    // Settings management
    public ?string $settingName = null;
    public ?string $settingDescription = null;
    public ?bool $settingIsActive = true;

    public function mount(): void
    {
        $this->apiKey = config('services.openai.api_key');
        $this->model = config('services.openai.model', 'gpt-4o-mini');
        $this->temperature = config('services.openai.temperature', 0.7);
        $this->maxTokens = config('services.openai.max_tokens', 4000);
        $this->systemPrompt = 'You are a helpful AI assistant.';
        $this->userPrompt = 'Hello! Can you help me with a simple question?';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)
                    ->schema([
                        // API Configuration Section
                        Section::make('API Configuration')
                            ->schema([
                                TextInput::make('apiKey')
                                    ->label('API Key')
                                    ->password()
                                    ->required()
                                    ->helperText('Your OpenAI API key (stored securely)'),

                                Select::make('model')
                                    ->label('Model')
                                    ->options([
                                        'gpt-4o' => 'GPT-4o',
                                        'gpt-4o-mini' => 'GPT-4o Mini',
                                        'gpt-4-turbo' => 'GPT-4 Turbo',
                                        'gpt-4' => 'GPT-4',
                                        'gpt-3.5-turbo' => 'GPT-3.5 Turbo',
                                    ])
                                    ->default('gpt-4o-mini')
                                    ->required(),

                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('temperature')
                                            ->label('Temperature')
                                            ->numeric()
                                            ->minValue(0)
                                            ->maxValue(2)
                                            ->step(0.1)
                                            ->default(0.7)
                                            ->helperText('Controls randomness (0-2)'),

                                        TextInput::make('maxTokens')
                                            ->label('Max Tokens')
                                            ->numeric()
                                            ->minValue(1)
                                            ->maxValue(32000)
                                            ->default(4000)
                                            ->helperText('Maximum response length'),
                                    ]),

                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('topP')
                                            ->label('Top P')
                                            ->numeric()
                                            ->minValue(0)
                                            ->maxValue(1)
                                            ->step(0.1)
                                            ->default(1.0)
                                            ->helperText('Nucleus sampling parameter'),

                                        TextInput::make('frequencyPenalty')
                                            ->label('Frequency Penalty')
                                            ->numeric()
                                            ->minValue(-2)
                                            ->maxValue(2)
                                            ->step(0.1)
                                            ->default(0.0)
                                            ->helperText('Reduces repetition'),
                                    ]),

                                TextInput::make('presencePenalty')
                                    ->label('Presence Penalty')
                                    ->numeric()
                                    ->minValue(-2)
                                    ->maxValue(2)
                                    ->step(0.1)
                                    ->default(0.0)
                                    ->helperText('Encourages new topics'),

                                Toggle::make('streamResponse')
                                    ->label('Stream Response')
                                    ->default(false)
                                    ->helperText('Enable streaming for real-time responses'),
                            ]),

                        // Test Configuration Section
                        Section::make('Test Configuration')
                            ->schema([
                                Select::make('testType')
                                    ->label('Test Type')
                                    ->options([
                                        'chat' => 'Chat Completion',
                                        'function_call' => 'Function Call',
                                        'vision' => 'Vision (Image Analysis)',
                                        'embedding' => 'Text Embedding',
                                        'moderation' => 'Content Moderation',
                                    ])
                                    ->default('chat')
                                    ->reactive(),

                                Textarea::make('systemPrompt')
                                    ->label('System Prompt')
                                    ->rows(3)
                                    ->placeholder('You are a helpful AI assistant...')
                                    ->helperText('System message to guide the AI behavior'),

                                Textarea::make('userPrompt')
                                    ->label('User Prompt')
                                    ->rows(4)
                                    ->required()
                                    ->placeholder('Enter your test prompt here...')
                                    ->helperText('The main prompt to test with the API'),

                                KeyValue::make('functionCall')
                                    ->label('Function Call (JSON Schema)')
                                    ->visible(fn () => $this->testType === 'function_call')
                                    ->keyLabel('Property')
                                    ->valueLabel('Type/Description')
                                    ->addActionLabel('Add Property')
                                    ->helperText('Define function parameters for function calling'),
                            ]),
                    ]),

                // Action Buttons
                Section::make('Actions')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                \Filament\Forms\Components\Actions::make([
                                    \Filament\Forms\Components\Actions\Action::make('testConnection')
                                        ->label('Test Connection')
                                        ->icon('heroicon-o-signal')
                                        ->color('success')
                                        ->action('testApiConnection'),

                                    \Filament\Forms\Components\Actions\Action::make('sendRequest')
                                        ->label('Send Request')
                                        ->icon('heroicon-o-paper-airplane')
                                        ->color('primary')
                                        ->action('sendApiRequest'),

                                    \Filament\Forms\Components\Actions\Action::make('saveSetting')
                                        ->label('Save as Setting')
                                        ->icon('heroicon-o-bookmark')
                                        ->color('warning')
                                        ->action('saveAsSetting'),
                                ]),
                            ]),
                    ]),
            ]);
    }

    public function testApiConnection(): void
    {
        $this->isLoading = true;
        $this->errorMessage = null;
        $this->apiResponse = null;

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->get('https://api.openai.com/v1/models');

            if ($response->successful()) {
                $models = $response->json('data', []);
                $availableModels = collect($models)->pluck('id')->toArray();

                $this->apiResponse = "✅ API Connection Successful!\n\n";
                $this->apiResponse .= "Available Models:\n";
                foreach ($availableModels as $model) {
                    $this->apiResponse .= "- {$model}\n";
                }

                $this->responseMetadata = [
                    'status' => $response->status(),
                    'models_count' => count($models),
                    'response_time' => $response->handlerStats()['total_time'] ?? 0,
                ];

                Notification::make()
                    ->title('API Connection Successful')
                    ->success()
                    ->send();
            } else {
                $this->errorMessage = "❌ API Connection Failed: " . $response->status() . " - " . $response->body();

                Notification::make()
                    ->title('API Connection Failed')
                    ->body($response->body())
                    ->danger()
                    ->send();
            }
        } catch (\Exception $e) {
            $this->errorMessage = "❌ Connection Error: " . $e->getMessage();

            Notification::make()
                ->title('Connection Error')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }

        $this->isLoading = false;
    }

    public function sendApiRequest(): void
    {
        $this->isLoading = true;
        $this->errorMessage = null;
        $this->apiResponse = null;

        try {
            $startTime = microtime(true);

            switch ($this->testType) {
                case 'chat':
                    $response = $this->sendChatRequest();
                    break;
                case 'function_call':
                    $response = $this->sendFunctionCallRequest();
                    break;
                case 'vision':
                    $response = $this->sendVisionRequest();
                    break;
                case 'embedding':
                    $response = $this->sendEmbeddingRequest();
                    break;
                case 'moderation':
                    $response = $this->sendModerationRequest();
                    break;
                default:
                    throw new \Exception('Invalid test type');
            }

            $executionTime = (microtime(true) - $startTime) * 1000;

            if ($response->successful()) {
                $data = $response->json();

                switch ($this->testType) {
                    case 'chat':
                        $this->apiResponse = $data['choices'][0]['message']['content'] ?? 'No content received';
                        break;
                    case 'function_call':
                        $this->apiResponse = json_encode($data['choices'][0]['message']['function_call'] ?? [], JSON_PRETTY_PRINT);
                        break;
                    case 'embedding':
                        $this->apiResponse = "Embedding generated successfully. Vector length: " . count($data['data'][0]['embedding'] ?? []);
                        break;
                    case 'moderation':
                        $this->apiResponse = json_encode($data['results'][0] ?? [], JSON_PRETTY_PRINT);
                        break;
                    default:
                        $this->apiResponse = json_encode($data, JSON_PRETTY_PRINT);
                }

                $this->responseMetadata = [
                    'status' => $response->status(),
                    'execution_time' => round($executionTime, 2),
                    'model' => $this->model,
                    'temperature' => $this->temperature,
                    'max_tokens' => $this->maxTokens,
                    'response_length' => strlen($this->apiResponse),
                ];

                // Log the successful request
                $this->logApiRequest($data, $executionTime);

                Notification::make()
                    ->title('API Request Successful')
                    ->success()
                    ->send();
            } else {
                $this->errorMessage = "❌ API Request Failed: " . $response->status() . " - " . $response->body();

                Notification::make()
                    ->title('API Request Failed')
                    ->body($response->body())
                    ->danger()
                    ->send();
            }
        } catch (\Exception $e) {
            $this->errorMessage = "❌ Request Error: " . $e->getMessage();

            Notification::make()
                ->title('Request Error')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }

        $this->isLoading = false;
    }

    private function sendChatRequest()
    {
        $payload = [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $this->systemPrompt
                ],
                [
                    'role' => 'user',
                    'content' => $this->userPrompt
                ]
            ],
            'max_tokens' => $this->maxTokens,
            'temperature' => $this->temperature,
            'top_p' => $this->topP,
            'frequency_penalty' => $this->frequencyPenalty,
            'presence_penalty' => $this->presencePenalty,
        ];

        if ($this->streamResponse) {
            $payload['stream'] = true;
        }

        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post('https://api.openai.com/v1/chat/completions', $payload);
    }

    private function sendFunctionCallRequest()
    {
        $functions = [
            [
                'name' => 'test_function',
                'description' => 'A test function for API testing',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'result' => [
                            'type' => 'string',
                            'description' => 'The result of the function call'
                        ],
                        'status' => [
                            'type' => 'string',
                            'enum' => ['success', 'error'],
                            'description' => 'The status of the operation'
                        ]
                    ],
                    'required' => ['result', 'status']
                ]
            ]
        ];

        $payload = [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $this->systemPrompt
                ],
                [
                    'role' => 'user',
                    'content' => $this->userPrompt
                ]
            ],
            'functions' => $functions,
            'function_call' => ['name' => 'test_function'],
            'max_tokens' => $this->maxTokens,
            'temperature' => $this->temperature,
        ];

        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post('https://api.openai.com/v1/chat/completions', $payload);
    }

    private function sendEmbeddingRequest()
    {
        $payload = [
            'model' => 'text-embedding-ada-002',
            'input' => $this->userPrompt,
        ];

        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post('https://api.openai.com/v1/embeddings', $payload);
    }

    private function sendModerationRequest()
    {
        $payload = [
            'input' => $this->userPrompt,
        ];

        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post('https://api.openai.com/v1/moderations', $payload);
    }

    private function sendVisionRequest()
    {
        // For vision requests, we'd need an image URL or base64 data
        // This is a placeholder implementation
        $payload = [
            'model' => 'gpt-4o',
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => $this->userPrompt
                        ]
                    ]
                ]
            ],
            'max_tokens' => $this->maxTokens,
        ];

        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post('https://api.openai.com/v1/chat/completions', $payload);
    }

    public function saveAsSetting(): void
    {
        $this->validate([
            'settingName' => 'required|string|max:255|unique:ai_generation_settings,name',
            'settingDescription' => 'nullable|string',
        ]);

        try {
            AIGenerationSetting::create([
                'name' => $this->settingName,
                'description' => $this->settingDescription,
                'model' => $this->model,
                'temperature' => $this->temperature,
                'max_tokens' => $this->maxTokens,
                'top_p' => $this->topP,
                'frequency_penalty' => $this->frequencyPenalty,
                'presence_penalty' => $this->presencePenalty,
                'system_prompt' => $this->systemPrompt,
                'is_active' => $this->settingIsActive,
                'additional_parameters' => [
                    'stream_response' => $this->streamResponse,
                ],
            ]);

            Notification::make()
                ->title('Setting Saved Successfully')
                ->success()
                ->send();

            // Reset form fields
            $this->settingName = null;
            $this->settingDescription = null;

        } catch (\Exception $e) {
            Notification::make()
                ->title('Failed to Save Setting')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    private function logApiRequest(array $response, float $executionTime): void
    {
        Log::info('OpenAI API Request', [
            'test_type' => $this->testType,
            'model' => $this->model,
            'temperature' => $this->temperature,
            'max_tokens' => $this->maxTokens,
            'execution_time' => $executionTime,
            'response_length' => strlen($this->apiResponse),
            'user_id' => auth()->id(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(AIGenerationSetting::query())
            ->columns([
                TextColumn::make('name')
                    ->label('Setting Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('model')
                    ->label('Model')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('temperature')
                    ->label('Temperature')
                    ->numeric(2),

                TextColumn::make('max_tokens')
                    ->label('Max Tokens')
                    ->numeric(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                TableAction::make('load')
                    ->label('Load')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function (AIGenerationSetting $setting) {
                        $this->loadSetting($setting);
                    }),

                TableAction::make('test')
                    ->label('Test')
                    ->icon('heroicon-o-play')
                    ->action(function (AIGenerationSetting $setting) {
                        $this->loadSetting($setting);
                        $this->sendApiRequest();
                    }),
            ])
            ->bulkActions([
                // Add bulk actions if needed
            ]);
    }

    public function loadSetting(AIGenerationSetting $setting): void
    {
        $this->model = $setting->model;
        $this->temperature = $setting->temperature;
        $this->maxTokens = $setting->max_tokens;
        $this->topP = $setting->top_p;
        $this->frequencyPenalty = $setting->frequency_penalty;
        $this->presencePenalty = $setting->presence_penalty;
        $this->systemPrompt = $setting->system_prompt;
        $this->streamResponse = $setting->additional_parameters['stream_response'] ?? false;

        Notification::make()
            ->title('Setting Loaded')
            ->body("Loaded setting: {$setting->name}")
            ->success()
            ->send();
    }

    public function getApiUsageStats(): array
    {
        // This would typically fetch from OpenAI's usage API
        // For now, we'll return mock data
        return [
            'total_requests' => Cache::get('openai_total_requests', 0),
            'total_tokens' => Cache::get('openai_total_tokens', 0),
            'cost_estimate' => Cache::get('openai_cost_estimate', 0),
            'requests_today' => Cache::get('openai_requests_today', 0),
        ];
    }

    // Template loading methods
    public function loadProposalTemplate(): void
    {
        $this->testType = 'chat';
        $this->model = 'gpt-4o-mini';
        $this->temperature = 0.7;
        $this->maxTokens = 2000;
        $this->systemPrompt = 'You are a professional business consultant specializing in creating compelling proposals.';
        $this->userPrompt = 'Create a professional proposal for a web development project. The client needs a modern e-commerce website with payment processing, inventory management, and customer analytics. Budget is $15,000-$25,000. Timeline is 8-12 weeks.';

        Notification::make()
            ->title('Proposal Template Loaded')
            ->success()
            ->send();
    }

    public function loadTaskTemplate(): void
    {
        $this->testType = 'function_call';
        $this->model = 'gpt-4o-mini';
        $this->temperature = 0.5;
        $this->maxTokens = 1500;
        $this->systemPrompt = 'You are a project manager. Create detailed tasks for web development projects.';
        $this->userPrompt = 'Create a task breakdown for developing a responsive website with the following features: homepage, about page, contact form, blog section, and admin dashboard.';

        Notification::make()
            ->title('Task Template Loaded')
            ->success()
            ->send();
    }

    public function loadCreativeTemplate(): void
    {
        $this->testType = 'chat';
        $this->model = 'gpt-4o';
        $this->temperature = 0.9;
        $this->maxTokens = 1000;
        $this->systemPrompt = 'You are a creative writer with a vivid imagination and engaging storytelling abilities.';
        $this->userPrompt = 'Write a short story about a time traveler who discovers they can only travel to moments when they were happy.';

        Notification::make()
            ->title('Creative Template Loaded')
            ->success()
            ->send();
    }

    public function loadCodeTemplate(): void
    {
        $this->testType = 'chat';
        $this->model = 'gpt-4o';
        $this->temperature = 0.3;
        $this->maxTokens = 2000;
        $this->systemPrompt = 'You are an expert software developer. Write clean, well-documented code with best practices.';
        $this->userPrompt = 'Write a Python function that implements a binary search algorithm. Include proper error handling and documentation.';

        Notification::make()
            ->title('Code Template Loaded')
            ->success()
            ->send();
    }

    public function loadAnalysisTemplate(): void
    {
        $this->testType = 'chat';
        $this->model = 'gpt-4o';
        $this->temperature = 0.2;
        $this->maxTokens = 1500;
        $this->systemPrompt = 'You are a data analyst. Provide clear, actionable insights from data.';
        $this->userPrompt = 'Analyze this sales data and provide insights: Q1: $50K, Q2: $65K, Q3: $45K, Q4: $80K. What trends do you see and what recommendations would you make?';

        Notification::make()
            ->title('Analysis Template Loaded')
            ->success()
            ->send();
    }

    public function loadTranslationTemplate(): void
    {
        $this->testType = 'chat';
        $this->model = 'gpt-4o';
        $this->temperature = 0.3;
        $this->maxTokens = 1000;
        $this->systemPrompt = 'You are a professional translator. Provide accurate and natural translations.';
        $this->userPrompt = 'Translate this English text to Spanish: "Welcome to our website! We are excited to help you find the perfect solution for your business needs."';

        Notification::make()
            ->title('Translation Template Loaded')
            ->success()
            ->send();
    }
}
