<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Client;
use App\Models\Proposal;
use App\Models\Project;
use App\Models\Task;
use App\Models\Subtask;
use App\Models\DashboardWidget;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Get user's dashboard widgets
        $widgets = DashboardWidget::where('user_id', $user->id)
            ->where('is_active', true)
            ->orderBy('position')
            ->get();

        // Get basic statistics for widget data
        $stats = [
            'totalClients' => Client::count(),
            'totalProposals' => Proposal::count(),
            'totalProjects' => Project::count(),
            'totalTasks' => Task::where('status', 'completed')->count(),
            'pendingTasks' => Task::whereIn('status', ['todo', 'in_progress'])->count(),
            'revenue' => $this->calculateRevenue(),
            'recentProposals' => $this->getRecentProposals(),
            'recentTasks' => $this->getRecentTasks(),
            'recentProjects' => $this->getRecentProjects(),
        ];

        // Add dynamic calculations based on widget configurations
        $stats = $this->addDynamicStats($stats);

        // Get clients for AI project generation
        $clients = Client::whereHas('users', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->get(['id', 'first_name', 'last_name', 'company_name']);

        return Inertia::render('Dashboard', [
            'auth' => [
                'user' => $user,
            ],
            'stats' => $stats,
            'clients' => $clients,
            'widgets' => $widgets,
        ]);
    }

    private function calculateRevenue()
    {
        // This is a placeholder - you would implement your actual revenue calculation
        // based on your business logic (e.g., from proposals, invoices, etc.)
        return 25000; // Example value
    }

    private function getRecentProposals()
    {
        return Proposal::with('client')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($proposal) {
                return [
                    'id' => $proposal->id,
                    'title' => $proposal->title,
                    'client_name' => $proposal->client ? $proposal->client->full_name : 'Unknown Client',
                    'status' => $proposal->status ?? 'draft',
                    'created_at' => $proposal->created_at->diffForHumans(),
                ];
            });
    }

    private function getRecentTasks()
    {
        return Task::with(['project', 'project.client'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'project_name' => $task->project->name ?? 'Unknown Project',
                    'status' => $task->status,
                    'priority' => $task->priority ?? 'medium',
                    'due_date' => $task->due_date ? $task->due_date->format('Y-m-d') : null,
                    'created_at' => $task->created_at->diffForHumans(),
                ];
            });
    }

        private function getRecentProjects()
    {
        return Project::with(['client', 'tasks'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($project) {
                $totalTasks = $project->tasks->count();
                $completedTasks = $project->tasks->where('status', 'done')->count();

                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'client' => $project->client ? $project->client->full_name : 'Unknown Client',
                    'status' => $project->status ?? 'planned',
                    'progress' => $project->progress ?? 0,
                    'due_date' => $project->due_date ? $project->due_date->format('Y-m-d') : null,
                    'tasks' => [
                        'completed' => $completedTasks,
                        'total' => $totalTasks
                    ],
                    'created_at' => $project->created_at->diffForHumans(),
                ];
            });
    }

    /**
     * Add dynamic statistics based on widget configurations
     */
    private function addDynamicStats(array $stats): array
    {
        $user = Auth::user();

        // Get user's widgets to understand what metrics they need
        $widgets = DashboardWidget::where('user_id', $user->id)
            ->where('is_active', true)
            ->where('widget_type', 'quick_stats')
            ->get();

        foreach ($widgets as $widget) {
            $config = $widget->configuration ?? [];
            $metricType = $config['metric_type'] ?? '';
            $metricFilter = $config['metric_filter'] ?? '';

            // Generate a unique key for this metric
            $metricKey = "dynamic_{$metricType}_{$metricFilter}";

            switch ($metricType) {
                case 'subtasks':
                    if ($metricFilter === 'assigned_to_user') {
                        // Get subtasks where the parent task is assigned to the user
                        $stats[$metricKey] = Subtask::whereHas('task', function($query) use ($user) {
                            $query->where('assigned_to', $user->id);
                        })->count();
                    } else {
                        $stats[$metricKey] = Subtask::count();
                    }
                    break;

                case 'tasks':
                    if ($metricFilter === 'pending') {
                        $stats[$metricKey] = Task::whereIn('status', ['todo', 'in_progress'])->count();
                    } elseif ($metricFilter === 'completed') {
                        $stats[$metricKey] = Task::where('status', 'completed')->count();
                    } else {
                        $stats[$metricKey] = Task::count();
                    }
                    break;

                case 'projects':
                    if ($metricFilter === 'active') {
                        $stats[$metricKey] = Project::whereIn('status', ['in_progress', 'active'])->count();
                    } else {
                        $stats[$metricKey] = Project::count();
                    }
                    break;

                case 'clients':
                    $stats[$metricKey] = Client::count();
                    break;

                case 'proposals':
                    if ($metricFilter === 'draft') {
                        $stats[$metricKey] = Proposal::where('status', 'draft')->count();
                    } elseif ($metricFilter === 'sent') {
                        $stats[$metricKey] = Proposal::where('status', 'sent')->count();
                    } elseif ($metricFilter === 'accepted') {
                        $stats[$metricKey] = Proposal::where('status', 'accepted')->count();
                    } else {
                        $stats[$metricKey] = Proposal::count();
                    }
                    break;

                case 'revenue':
                    // Revenue widget removed
                    $stats[$metricKey] = 0;
                    break;
            }
        }

        return $stats;
    }

    /**
     * Calculate monthly revenue
     */
    private function calculateMonthlyRevenue()
    {
        // This is a placeholder - implement your actual monthly revenue calculation
        return 25000; // Example value
    }
}
