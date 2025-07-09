<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AIGenerationLogResource\Pages;
use App\Filament\Resources\AIGenerationLogResource\RelationManagers;
use App\Models\AIGenerationLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AIGenerationLogResource extends Resource
{
    protected static ?string $model = AIGenerationLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'AI Generation Logs';

    protected static ?string $modelLabel = 'AI Generation Log';

    protected static ?string $pluralModelLabel = 'AI Generation Logs';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('generation_type')
                    ->options([
                        'proposal' => 'Proposal',
                        'project' => 'Project',
                        'task' => 'Task',
                        'subtask' => 'Subtask',
                        'service' => 'Service',
                        'package' => 'Package',
                    ])
                    ->required(),

                Forms\Components\Textarea::make('prompt')
                    ->rows(4)
                    ->required(),

                Forms\Components\Textarea::make('response')
                    ->rows(8)
                    ->required(),

                Forms\Components\TextInput::make('model')
                    ->required(),

                Forms\Components\TextInput::make('prompt_tokens')
                    ->numeric(),

                Forms\Components\TextInput::make('completion_tokens')
                    ->numeric(),

                Forms\Components\TextInput::make('total_tokens')
                    ->numeric(),

                Forms\Components\TextInput::make('temperature')
                    ->numeric()
                    ->step(0.1),

                Forms\Components\TextInput::make('max_tokens')
                    ->numeric(),

                Forms\Components\TextInput::make('execution_time_ms')
                    ->numeric(),

                Forms\Components\Select::make('status')
                    ->options([
                        'success' => 'Success',
                        'error' => 'Error',
                        'partial' => 'Partial',
                    ])
                    ->required(),

                Forms\Components\Textarea::make('error_message')
                    ->rows(3),

                Forms\Components\KeyValue::make('metadata'),

                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('generation_type')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('model')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('prompt')
                    ->limit(50)
                    ->searchable(),

                Tables\Columns\TextColumn::make('total_tokens')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('execution_time_ms')
                    ->numeric()
                    ->sortable()
                    ->label('Time (ms)'),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'success',
                        'error' => 'danger',
                        'partial' => 'warning',
                    ]),

                Tables\Columns\TextColumn::make('user.name')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('generation_type')
                    ->options([
                        'proposal' => 'Proposal',
                        'project' => 'Project',
                        'task' => 'Task',
                        'subtask' => 'Subtask',
                        'service' => 'Service',
                        'package' => 'Package',
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'success' => 'Success',
                        'error' => 'Error',
                        'partial' => 'Partial',
                    ]),

                Tables\Filters\SelectFilter::make('model')
                    ->options([
                        'gpt-4' => 'GPT-4',
                        'gpt-4-turbo' => 'GPT-4 Turbo',
                        'gpt-3.5-turbo' => 'GPT-3.5 Turbo',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListAIGenerationLogs::route('/'),
            'create' => Pages\CreateAIGenerationLog::route('/create'),
            'edit' => Pages\EditAIGenerationLog::route('/{record}/edit'),
        ];
    }
}
