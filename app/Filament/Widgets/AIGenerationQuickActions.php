<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Filament\Actions\Action;

class AIGenerationQuickActions extends Widget
{
    protected static string $view = 'filament.widgets.ai-generation-quick-actions';

    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    protected function getViewData(): array
    {
        return [
            'actions' => [
                [
                    'label' => 'Generate Proposal',
                    'icon' => 'heroicon-o-document-text',
                    'color' => 'primary',
                    'url' => '/admin/ai-generation?action=proposal',
                ],
                [
                    'label' => 'Generate Project Plan',
                    'icon' => 'heroicon-o-calendar',
                    'color' => 'success',
                    'url' => '/admin/ai-generation?action=project',
                ],
                [
                    'label' => 'Generate Task',
                    'icon' => 'heroicon-o-check-circle',
                    'color' => 'warning',
                    'url' => '/admin/ai-generation?action=task',
                ],
                [
                    'label' => 'Generate Service',
                    'icon' => 'heroicon-o-cog',
                    'color' => 'info',
                    'url' => '/admin/ai-generation?action=service',
                ],
                [
                    'label' => 'View All Logs',
                    'icon' => 'heroicon-o-list-bullet',
                    'color' => 'gray',
                    'url' => '/admin/a-i-generation-logs',
                ],
                [
                    'label' => 'Manage Settings',
                    'icon' => 'heroicon-o-cog-6-tooth',
                    'color' => 'gray',
                    'url' => '/admin/a-i-generation-settings',
                ],
            ],
        ];
    }
}
