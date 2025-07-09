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
                        'custom' => 'Custom',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('description'),
                Forms\Components\Textarea::make('template')
                    ->rows(8)
                    ->required(),
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
                        'custom' => 'Custom',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')->label('Active'),
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
            'index' => Pages\ListPromptTemplates::route('/'),
            'create' => Pages\CreatePromptTemplate::route('/create'),
            'edit' => Pages\EditPromptTemplate::route('/{record}/edit'),
        ];
    }
}
