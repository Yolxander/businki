<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Client;
use App\Models\Proposal;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Get basic statistics
        $stats = [
            'totalClients' => Client::count(),
            'totalProposals' => Proposal::count(),
            'totalProjects' => Project::count(),
            'totalTasks' => Task::where('status', 'completed')->count(),
            'pendingTasks' => Task::where('status', 'pending')->count(),
            'revenue' => $this->calculateRevenue(),
            'recentProposals' => $this->getRecentProposals(),
            'recentTasks' => $this->getRecentTasks(),
        ];

        return Inertia::render('Dashboard', [
            'auth' => [
                'user' => $user,
            ],
            'stats' => $stats,
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
                    'client_name' => $proposal->client->name ?? 'Unknown Client',
                    'status' => $proposal->status ?? 'draft',
                    'created_at' => $proposal->created_at->diffForHumans(),
                ];
            });
    }

    private function getRecentTasks()
    {
        return Task::with('project')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'project_name' => $task->project->name ?? 'Unknown Project',
                    'status' => $task->status,
                    'created_at' => $task->created_at->diffForHumans(),
                ];
            });
    }
}
