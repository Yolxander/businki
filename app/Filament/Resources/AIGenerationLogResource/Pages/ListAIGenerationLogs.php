<?php

namespace App\Filament\Resources\AIGenerationLogResource\Pages;

use App\Filament\Resources\AIGenerationLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAIGenerationLogs extends ListRecords
{
    protected static string $resource = AIGenerationLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
