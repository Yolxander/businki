<?php

namespace App\Filament\Resources\PromptTemplateResource\Pages;

use App\Filament\Resources\PromptTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePromptTemplate extends CreateRecord
{
    protected static string $resource = PromptTemplateResource::class;

    public static function rules(): array
    {
        return [
            'template' => ['required', 'string'],
        ];
    }
}
