<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AIGenerationSettingResource\Pages;
use App\Filament\Resources\AIGenerationSettingResource\RelationManagers;
use App\Models\AIGenerationSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AIGenerationSettingResource extends Resource
{
    protected static ?string $model = AIGenerationSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'AI Generation Settings';
    protected static ?string $modelLabel = 'AI Generation Setting';
    protected static ?string $pluralModelLabel = 'AI Generation Settings';
    protected static ?string $navigationGroup = 'AI Generation';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->placeholder('e.g., default, proposal, task'),

                Forms\Components\Select::make('model')
                    ->options([
                        'gpt-4' => 'GPT-4',
                        'gpt-4-turbo' => 'GPT-4 Turbo',
                        'gpt-3.5-turbo' => 'GPT-3.5 Turbo',
                    ])
                    ->required()
                    ->default('gpt-4'),

                Forms\Components\TextInput::make('temperature')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(2)
                    ->step(0.1)
                    ->default(0.7)
                    ->required(),

                Forms\Components\TextInput::make('max_tokens')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(8000)
                    ->default(4000)
                    ->required(),

                Forms\Components\TextInput::make('top_p')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(1)
                    ->step(0.1)
                    ->default(1.0)
                    ->required(),

                Forms\Components\TextInput::make('frequency_penalty')
                    ->numeric()
                    ->minValue(-2)
                    ->maxValue(2)
                    ->step(0.1)
                    ->default(0)
                    ->required(),

                Forms\Components\TextInput::make('presence_penalty')
                    ->numeric()
                    ->minValue(-2)
                    ->maxValue(2)
                    ->step(0.1)
                    ->default(0)
                    ->required(),

                Forms\Components\Textarea::make('system_prompt')
                    ->rows(4)
                    ->placeholder('Optional system prompt to guide the AI behavior'),

                Forms\Components\KeyValue::make('additional_parameters')
                    ->label('Additional Parameters')
                    ->keyLabel('Parameter Name')
                    ->valueLabel('Parameter Value'),

                Forms\Components\Toggle::make('is_active')
                    ->default(true)
                    ->required(),

                Forms\Components\Textarea::make('description')
                    ->rows(3)
                    ->placeholder('Description of when to use this setting'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('model')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('temperature')
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )
                    ->sortable(),

                Tables\Columns\TextColumn::make('max_tokens')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->searchable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('model')
                    ->options([
                        'gpt-4' => 'GPT-4',
                        'gpt-4-turbo' => 'GPT-4 Turbo',
                        'gpt-3.5-turbo' => 'GPT-3.5 Turbo',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),
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
            'index' => Pages\ListAIGenerationSettings::route('/'),
            'create' => Pages\CreateAIGenerationSetting::route('/create'),
            'edit' => Pages\EditAIGenerationSetting::route('/{record}/edit'),
        ];
    }
}
