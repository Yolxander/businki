<?php

namespace App\Filament\Pages;

use App\Models\AIGenerationSetting;
use App\Services\OpenAIService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
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
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state) {
                                            $setting = AIGenerationSetting::where('name', $state)->first();
                                            if ($setting) {
                                                $set('model', $setting->model);
                                                $set('temperature', $setting->temperature);
                                                $set('max_tokens', $setting->max_tokens);
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

                        Section::make('Quick Actions')
                            ->description('Common generation tasks')
                            ->schema([
                                Action::make('generateProposal')
                                    ->label('Generate Proposal')
                                    ->action('generateProposal')
                                    ->color('primary')
                                    ->icon('heroicon-o-document-text'),

                                Action::make('generateProject')
                                    ->label('Generate Project Plan')
                                    ->action('generateProject')
                                    ->color('success')
                                    ->icon('heroicon-o-calendar'),

                                Action::make('generateTask')
                                    ->label('Generate Task')
                                    ->action('generateTask')
                                    ->color('warning')
                                    ->icon('heroicon-o-check-circle'),

                                Action::make('generateService')
                                    ->label('Generate Service')
                                    ->action('generateService')
                                    ->color('info')
                                    ->icon('heroicon-o-cog'),
                            ])
                            ->columns(2),
                    ]),

                Section::make('Manual Generation')
                    ->description('Test and trigger AI content generation manually')
                    ->schema([
                        Textarea::make('prompt')
                            ->label('Prompt')
                            ->placeholder('Enter your prompt here...')
                            ->rows(6)
                            ->required(),
                    ]),
            ]);
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
            Action::make('generate')
                ->label('Generate Content')
                ->action('generate')
                ->color('primary')
                ->icon('heroicon-o-sparkles'),
        ];
    }
}
