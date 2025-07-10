<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PromptTemplateResource\Pages;
use App\Filament\Resources\PromptTemplateResource\RelationManagers;
use App\Models\PromptTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;

class PromptTemplateResource extends Resource
{
    protected static ?string $model = PromptTemplate::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'AI Generation';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\Select::make('type')
                    ->options([
                        'proposal' => 'Proposal',
                        'project' => 'Project',
                        'task' => 'Task',
                        'subtask' => 'Subtask',
                        'service' => 'Service',
                        'package' => 'Package',
                        'personal_project' => 'Personal Project',
                        'personal_task' => 'Personal Task',
                        'custom' => 'Custom',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('description'),
                                Forms\Components\Section::make('Template')
                    ->schema([
                        Forms\Components\Textarea::make('template')
                            ->label(false)
                            ->rows(8)
                            ->columnSpanFull(),
                    ])
                    ->headerActions([
                        \Filament\Forms\Components\Actions\Action::make('generateNew')
                            ->label('Generate')
                            ->icon('heroicon-o-sparkles')
                            ->color('primary')
                            ->size('sm')
                            ->action(function ($record, $get, $set) {
                                try {
                                    $openaiService = new \App\Services\OpenAIService();
                                    $templateType = $get('type');
                                    $templateName = $get('name');
                                    $templateDescription = $get('description');
                                    if (!$record) { // CREATE PAGE
                                        // Compose a prompt for OpenAI to generate a new template with variables for the selected type
                                        $prompt = "You are a professional prompt template designer. Generate a new prompt template for the following type. Include variables as curly-brace placeholders (e.g., {full_name}, {company_name}) that are typical for this type. Do not include explanations, only the template text.\n\n";
                                        $prompt .= "Template Type: {$templateType}\n";
                                        $prompt .= "Template Name: {$templateName}\n";
                                        if ($templateDescription) {
                                            $prompt .= "Description: {$templateDescription}\n";
                                        }
                                        $prompt .= "\nRequirements:\n- Use variables relevant to the template type.\n- Use curly braces for variables.\n- Make the template professional and ready to use.\n- Do not include explanations or extra text.";

                                        $response = \Illuminate\Support\Facades\Http::withHeaders([
                                            'Authorization' => 'Bearer ' . config('services.openai.api_key'),
                                            'Content-Type' => 'application/json',
                                        ])->post('https://api.openai.com/v1/chat/completions', [
                                            'model' => config('services.openai.model'),
                                            'messages' => [
                                                [
                                                    'role' => 'system',
                                                    'content' => 'You are a professional prompt template designer.'
                                                ],
                                                [
                                                    'role' => 'user',
                                                    'content' => $prompt
                                                ]
                                            ],
                                            'max_tokens' => 600,
                                            'temperature' => 0.7,
                                        ]);

                                        if (!$response->successful()) {
                                            throw new \Exception('OpenAI API request failed: ' . $response->status());
                                        }
                                        $data = $response->json();
                                        $generatedTemplate = $data['choices'][0]['message']['content'] ?? '';
                                        if (empty($generatedTemplate)) {
                                            throw new \Exception('No content returned from OpenAI');
                                        }
                                        $set('template', trim($generatedTemplate));
                                        \Filament\Notifications\Notification::make()
                                            ->title('Template Generated')
                                            ->body('A new template has been generated with variables included.')
                                            ->success()
                                            ->send();
                                    } else { // EDIT PAGE
                                        $currentTemplate = $get('template');
                                        $newTemplate = $openaiService->regenerateTemplate(
                                            $currentTemplate,
                                            $templateType,
                                            $templateName
                                        );
                                        $set('template', $newTemplate);
                                        \Filament\Notifications\Notification::make()
                                            ->title('Template Regenerated')
                                            ->body('The template has been successfully regenerated while preserving all variables.')
                                            ->success()
                                            ->send();
                                    }
                                } catch (\Exception $e) {
                                    \Filament\Notifications\Notification::make()
                                        ->title('Generation Failed')
                                        ->body('Failed to generate template: ' . $e->getMessage())
                                        ->danger()
                                        ->send();
                                }
                            })
                            ->requiresConfirmation()
                            ->modalHeading('Generate Template')
                            ->modalDescription('This will generate a new template with variables included, based only on the Name, Type, and Description you provide. Are you sure you want to continue?')
                            ->modalSubmitActionLabel('Generate'),
                    ]),
                Forms\Components\Toggle::make('is_active')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('type')->sortable(),
                Tables\Columns\TextColumn::make('description')->limit(40),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'proposal' => 'Proposal',
                        'project' => 'Project',
                        'task' => 'Task',
                        'subtask' => 'Subtask',
                        'service' => 'Service',
                        'package' => 'Package',
                        'personal_project' => 'Personal Project',
                        'personal_task' => 'Personal Task',
                        'custom' => 'Custom',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')->label('Active'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Action::make('preview')
                    ->label('Preview')
                    ->icon('heroicon-o-eye')
                    ->form([
                        Forms\Components\KeyValue::make('sample_data')
                            ->label('Sample Data')
                            ->helperText('Enter variable values to preview the rendered prompt.'),
                    ])
                    ->action(function (array $data, PromptTemplate $record) {
                        $rendered = $record->renderPrompt($data['sample_data'] ?? []);
                        Notification::make()
                            ->title('Prompt Preview')
                            ->body('<pre style="white-space: pre-wrap;">' . e($rendered) . '</pre>')
                            ->success()
                            ->send();
                    })
                    ->modalSubmitActionLabel('Preview')
                    ->modalWidth('xl'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPromptTemplates::route('/'),
            'create' => Pages\CreatePromptTemplate::route('/create'),
            'edit' => Pages\EditPromptTemplate::route('/{record}/edit'),
        ];
    }
}
