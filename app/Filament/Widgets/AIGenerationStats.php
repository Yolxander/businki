<?php

namespace App\Filament\Widgets;

use App\Models\AIGenerationLog;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AIGenerationStats extends BaseWidget
{
    protected function getStats(): array
    {
        // Get stats from the database (will work once migrations are run)
        $totalGenerations = AIGenerationLog::count();
        $successfulGenerations = AIGenerationLog::where('status', 'success')->count();
        $errorRate = $totalGenerations > 0 ? round((($totalGenerations - $successfulGenerations) / $totalGenerations) * 100, 1) : 0;
        $avgExecutionTime = AIGenerationLog::where('status', 'success')->avg('execution_time_ms') ?? 0;
        $totalTokens = AIGenerationLog::sum('total_tokens') ?? 0;

        return [
            Stat::make('Total Generations', $totalGenerations)
                ->description('All AI generation attempts')
                ->descriptionIcon('heroicon-m-sparkles')
                ->color('primary'),

            Stat::make('Success Rate', (100 - $errorRate) . '%')
                ->description('Successful generations')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Avg Response Time', round($avgExecutionTime, 0) . 'ms')
                ->description('Average execution time')
                ->descriptionIcon('heroicon-m-clock')
                ->color('info'),

            Stat::make('Total Tokens Used', number_format($totalTokens))
                ->description('Cumulative token usage')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('warning'),
        ];
    }
}
