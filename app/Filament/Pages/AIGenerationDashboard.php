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
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class AIGenerationDashboard extends Page
{
    protected static string $view = 'filament.pages.a-i-generation-dashboard';

    protected static ?string $title = 'AI Generation';

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationLabel = 'AI Generation';

    protected static ?string $navigationGroup = 'AI Generation';

    protected static ?string $slug = 'ai-generation';

    protected static ?int $navigationSort = 1;

    public ?array $data = [];
    public ?string $generatedContent = null;
    public ?array $generationStats = null;

    public function mount(): void
    {
        $this->form->fill([
            'generation_type' => 'proposal',
            'setting_name' => 'default',
            'model' => 'gpt-4',
            'temperature' => 0.7,
            'max_tokens' => 4000,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)
                    ->schema([
                        Section::make('Generation Settings')
                            ->description('Configure AI generation parameters')
                            ->schema([
                                Select::make('setting_name')
                                    ->label('Preset Settings')
                                    ->options(AIGenerationSetting::where('is_active', true)->pluck('description', 'name'))
                                    ->reactive()
                                    ->afterStateUpdated(function (
                                        $state, callable $set, callable $get) {
                                        if ($state) {
                                            $setting = AIGenerationSetting::where('name', $state)->first();
                                            if ($setting) {
                                                $set('model', $setting->model);
                                                $set('temperature', $setting->temperature);
                                                $set('max_tokens', $setting->max_tokens);
                                                // Also update prompt if a template exists for the type
                                                $type = $get('generation_type');
                                                $template = \App\Models\PromptTemplate::where('type', $type)->where('is_active', true)->first();
                                                if ($template) {
                                                    $set('prompt_template_id', $template->id);
                                                    $set('prompt', $template->template);
                                                }
                                            }
                                        }
                                    }),

                                Select::make('generation_type')
                                    ->label('Generation Type')
                                    ->options([
                                        'proposal' => 'Proposal',
                                        'project' => 'Project',
                                        'task' => 'Task',
                                        'subtask' => 'Subtask',
                                        'service' => 'Service',
                                        'package' => 'Package',
                                        'custom' => 'Custom',
                                    ])
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        // When generation type changes, update prompt template dropdown
                                        $template = \App\Models\PromptTemplate::where('type', $state)->where('is_active', true)->first();
                                        if ($template) {
                                            $set('prompt_template_id', $template->id);
                                            $set('prompt', $template->template);
                                        }
                                    })
                                    ->required(),

                                Select::make('model')
                                    ->label('Model')
                                    ->options([
                                        'gpt-4' => 'GPT-4',
                                        'gpt-4-turbo' => 'GPT-4 Turbo',
                                        'gpt-3.5-turbo' => 'GPT-3.5 Turbo',
                                    ])
                                    ->required(),

                                TextInput::make('temperature')
                                    ->label('Temperature')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(2)
                                    ->step(0.1)
                                    ->required(),

                                TextInput::make('max_tokens')
                                    ->label('Max Tokens')
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(8000)
                                    ->required(),
                            ])
                            ->columns(2),
                    ]),
                Section::make('Prompt')
                    ->description('Enter the prompt to send to OpenAI')
                    ->schema([
                        Select::make('prompt_template_id')
                            ->label('Prompt Template')
                            ->options(function () {
                                return \App\Models\PromptTemplate::where('is_active', true)
                                    ->pluck('name', 'id');
                            })
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $template = \App\Models\PromptTemplate::find($state);
                                    if ($template) {
                                        $set('prompt', $template->template);
                                    }
                                }
                            }),
                        Textarea::make('prompt')
                            ->label('Prompt')
                            ->rows(6)
                            ->required()
                            ->default('Write a short introduction about AI for business.'),
                    ]),
            ]);
    }

    public function preview(): void
    {
        $data = $this->form->getState();
        $prompt = $data['prompt'] ?? '';

        // Check if there's a prompt template selected
        if (!empty($data['prompt_template_id'])) {
            $template = \App\Models\PromptTemplate::find($data['prompt_template_id']);
            if ($template) {
                // Show a modal to collect sample data for template variables
                $this->dispatch('open-modal', id: 'preview-prompt-modal');
                return;
            }
        }

        // If no template or simple prompt, show the prompt as-is
        $this->showPromptPreview($prompt);
    }

    public function previewWithTemplate(array $sampleData): void
    {
        $data = $this->form->getState();

        if (!empty($data['prompt_template_id'])) {
            $template = \App\Models\PromptTemplate::find($data['prompt_template_id']);
            if ($template) {
                $renderedPrompt = $template->renderPrompt($sampleData);
                $this->showPromptPreview($renderedPrompt);
            }
        }
    }

    protected function showPromptPreview(string $prompt): void
    {
        Notification::make()
            ->title('Prompt Preview')
            ->body('<div class="bg-gray-100 p-4 rounded-lg"><pre class="whitespace-pre-wrap text-sm">' . e($prompt) . '</pre></div>')
            ->success()
            ->persistent()
            ->send();
    }

    public function generate(): void
    {
        $data = $this->form->getState();

        try {
            $openaiService = app(OpenAIService::class);

            $startTime = microtime(true);

            $response = $openaiService->generateContent(
                $data['prompt'],
                $data['model'],
                $data['temperature'],
                $data['max_tokens']
            );

            $executionTime = (microtime(true) - $startTime) * 1000;

            $this->generatedContent = $response;
            $this->generationStats = [
                'execution_time' => round($executionTime, 2),
                'response_length' => strlen($response),
                'model' => $data['model'],
                'temperature' => $data['temperature'],
            ];

            // Log the generation
            $this->logGeneration($data, $response, $executionTime);

            Notification::make()
                ->title('Generation Successful')
                ->success()
                ->send();

        } catch (\Exception $e) {
            Log::error('AI Generation failed', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);

            Notification::make()
                ->title('Generation Failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function generateProposal(): void
    {
        $this->form->fill([
            'generation_type' => 'proposal',
            'setting_name' => 'proposal',
            'prompt' => 'Generate a professional business proposal for a web development project. Include project scope, timeline, deliverables, and pricing.',
        ]);

        $this->generate();
    }

    public function generateProject(): void
    {
        $this->form->fill([
            'generation_type' => 'project',
            'setting_name' => 'project',
            'prompt' => 'Create a comprehensive project plan for a mobile app development project. Include phases, tasks, milestones, and resource requirements.',
        ]);

        $this->generate();
    }

    public function generateTask(): void
    {
        $this->form->fill([
            'generation_type' => 'task',
            'setting_name' => 'task',
            'prompt' => 'Create a detailed task description for implementing user authentication in a web application. Include requirements, acceptance criteria, and estimated effort.',
        ]);

        $this->generate();
    }

    public function generateService(): void
    {
        $this->form->fill([
            'generation_type' => 'service',
            'setting_name' => 'default',
            'prompt' => 'Create a service description for a digital marketing agency offering SEO, social media management, and content creation services.',
        ]);

        $this->generate();
    }

    protected function logGeneration(array $data, string $response, float $executionTime): void
    {
        // This will be implemented once the migration is resolved
        // For now, we'll just log to the application log
        Log::info('AI Generation completed', [
            'type' => $data['generation_type'],
            'model' => $data['model'],
            'execution_time' => $executionTime,
            'response_length' => strlen($response),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->label('Preview Prompt')
                ->icon('heroicon-o-eye')
                ->action('preview')
                ->color('gray')
                ->form([
                    KeyValue::make('sample_data')
                        ->label('Sample Data')
                        ->helperText('Enter variable values to preview the rendered prompt.'),
                ])
                ->action(function (array $data) {
                    $this->previewWithTemplate($data['sample_data'] ?? []);
                })
                ->modalSubmitActionLabel('Preview')
                ->modalWidth('lg'),
            Action::make('generate')
                ->label('Generate Content')
                ->action('generate')
                ->color('primary')
                ->icon('heroicon-o-sparkles'),
        ];
    }
}
