<?php

namespace App\Filament\Resources\PromptTemplateResource\Pages;

use App\Filament\Resources\PromptTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPromptTemplate extends EditRecord
{
    protected static string $resource = PromptTemplateResource::class;

    public static function rules(): array
    {
        return [
            'template' => ['required', 'string'],
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
