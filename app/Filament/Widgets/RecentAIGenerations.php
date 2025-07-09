<?php

namespace App\Filament\Widgets;

use App\Models\AIGenerationLog;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentAIGenerations extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                AIGenerationLog::query()
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('generation_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'proposal' => 'primary',
                        'project' => 'success',
                        'task' => 'warning',
                        'service' => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('model')
                    ->label('Model')
                    ->searchable(),

                Tables\Columns\TextColumn::make('prompt')
                    ->label('Prompt')
                    ->limit(50)
                    ->searchable(),

                Tables\Columns\TextColumn::make('total_tokens')
                    ->label('Tokens')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('execution_time_ms')
                    ->label('Time (ms)')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'success',
                        'error' => 'danger',
                        'partial' => 'warning',
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Generated')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn (AIGenerationLog $record): string => route('filament.admin.resources.a-i-generation-logs.edit', $record)),
            ]);
    }
}
