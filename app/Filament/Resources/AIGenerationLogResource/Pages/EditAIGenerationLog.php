<?php

namespace App\Filament\Resources\AIGenerationLogResource\Pages;

use App\Filament\Resources\AIGenerationLogResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAIGenerationLog extends EditRecord
{
    protected static string $resource = AIGenerationLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
