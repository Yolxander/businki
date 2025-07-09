<?php

namespace App\Filament\Resources\AIGenerationSettingResource\Pages;

use App\Filament\Resources\AIGenerationSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAIGenerationSettings extends ListRecords
{
    protected static string $resource = AIGenerationSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
