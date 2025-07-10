<?php

namespace App\Filament\Widgets;

use App\Models\AIGenerationLog;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class OpenAIUsageStats extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $totalRequests = Cache::get('openai_total_requests', 0);
        $totalTokens = Cache::get('openai_total_tokens', 0);
        $costEstimate = Cache::get('openai_cost_estimate', 0);
        $requestsToday = Cache::get('openai_requests_today', 0);

        // Get recent activity from logs
        $recentLogs = AIGenerationLog::where('created_at', '>=', now()->subDays(7))->count();
        $successRate = AIGenerationLog::where('created_at', '>=', now()->subDays(7))
            ->where('status', 'success')
            ->count();

        $successPercentage = $recentLogs > 0 ? round(($successRate / $recentLogs) * 100, 1) : 0;

        return [
            Stat::make('Total API Requests', $totalRequests)
                ->description('All time requests')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Today\'s Requests', $requestsToday)
                ->description('Requests in last 24 hours')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),

            Stat::make('Estimated Cost', '$' . number_format($costEstimate, 2))
                ->description('Total estimated API cost')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('warning'),

            Stat::make('Success Rate (7d)', $successPercentage . '%')
                ->description('Successful requests in last 7 days')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color($successPercentage >= 95 ? 'success' : ($successPercentage >= 80 ? 'warning' : 'danger')),
        ];
    }
}
