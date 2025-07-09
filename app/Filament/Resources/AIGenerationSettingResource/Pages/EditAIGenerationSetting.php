<?php

namespace App\Filament\Resources\AIGenerationSettingResource\Pages;

use App\Filament\Resources\AIGenerationSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAIGenerationSetting extends EditRecord
{
    protected static string $resource = AIGenerationSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
