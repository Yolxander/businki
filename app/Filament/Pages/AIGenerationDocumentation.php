<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class AIGenerationDocumentation extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static string $view = 'filament.pages.ai-generation-documentation';
    protected static ?string $title = 'AI Generation Documentation';
    protected static ?string $slug = 'ai-generation/documentation';
    protected static ?string $navigationGroup = 'AI Generation';

    public string $activeTab = 'overview';

    public function getViewData(): array
    {
        return [
            'activeTab' => $this->activeTab,
        ];
    }
}
