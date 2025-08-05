<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Project;
use App\Models\Task;
use App\Models\Proposal;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CalendarController extends Controller
{
        public function index()
    {
        $user = Auth::user();

        if (!$user) {
            \Log::error('No authenticated user found in CalendarController');
            return redirect()->route('login');
        }

        \Log::info('CalendarController called for user: ' . $user->id);

        // Get projects with due dates - show projects created by the user
        $projects = Project::whereNotNull('due_date')
            ->where('user_id', $user->id)
            ->with('client')
            ->get()
            ->map(function ($project) {
                $clientName = 'No Client';
                if ($project->client) {
                    $client = $project->client;
                    $clientName = $client->company_name ?? $client->first_name . ' ' . $client->last_name;
                }

                return [
                    'id' => $project->id,
                    'title' => $project->name,
                    'type' => 'project',
                    'date' => $project->due_date->format('Y-m-d'),
                    'client' => $clientName,
                    'priority' => $project->priority ?? 'medium',
                    'status' => $project->status,
                    'description' => $project->description,
                    'progress' => $project->progress ?? 0,
                    'url' => "/projects/{$project->id}"
                ];
            })
            ->toArray();

        // Get tasks with due dates - show tasks that are:
        // 1. Directly assigned to the user
        // 2. Connected to projects owned by the user
        // 3. Created by the user
        $userProjectIds = Project::where('user_id', $user->id)
            ->pluck('id')
            ->toArray();

        $tasks = Task::whereNotNull('due_date')
            ->where(function($query) use ($user, $userProjectIds) {
                $query->where('assigned_to', $user->id)
                      ->orWhere('user_id', $user->id)
                      ->orWhereIn('project_id', $userProjectIds);
            })
            ->with(['project.client'])
            ->get()
            ->map(function ($task) {
                $clientName = 'No Client';
                if ($task->project && $task->project->client) {
                    $client = $task->project->client;
                    $clientName = $client->company_name ?? $client->first_name . ' ' . $client->last_name;
                } elseif ($task->project) {
                    $clientName = 'No Client';
                } else {
                    $clientName = 'No Project';
                }

                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'type' => 'task',
                    'date' => $task->due_date->format('Y-m-d'),
                    'client' => $clientName,
                    'priority' => $task->priority ?? 'medium',
                    'status' => $task->status,
                    'description' => $task->description,
                    'project' => $task->project ? $task->project->name : 'No Project',
                    'url' => "/tasks/{$task->id}"
                ];
            })
            ->toArray();

        // Get proposals with valid_until dates - show proposals created by the user
        $proposals = Proposal::whereNotNull('valid_until')
            ->where('user_id', $user->id)
            ->with('client')
            ->get()
            ->map(function ($proposal) {
                $clientName = 'No Client';
                if ($proposal->client) {
                    $client = $proposal->client;
                    $clientName = $client->company_name ?? $client->first_name . ' ' . $client->last_name;
                }

                return [
                    'id' => $proposal->id,
                    'title' => $proposal->title,
                    'type' => 'proposal',
                    'date' => $proposal->valid_until->format('Y-m-d'),
                    'client' => $clientName,
                    'priority' => 'high', // Proposals are typically high priority
                    'status' => $proposal->status,
                    'description' => $proposal->description,
                    'price' => $proposal->price,
                    'url' => "/proposals/{$proposal->id}"
                ];
            })
            ->toArray();

        // Combine all events
        $events = array_merge($projects, $tasks, $proposals);

        // Sort events by date
        usort($events, function ($a, $b) {
            return strcmp($a['date'], $b['date']);
        });

        // Get upcoming events (next 7 days)
        $upcomingEvents = array_filter($events, function ($event) {
            $eventDate = Carbon::parse($event['date']);
            $today = Carbon::today();
            $nextWeek = Carbon::today()->addDays(7);
            return $eventDate->gte($today) && $eventDate->lte($nextWeek);
        });

        // Take only the first 10 upcoming events
        $upcomingEvents = array_slice($upcomingEvents, 0, 10);

        // Debug logging
        \Log::info('Calendar data:', [
            'user_id' => $user->id,
            'projects_count' => count($projects),
            'tasks_count' => count($tasks),
            'proposals_count' => count($proposals),
            'total_events' => count($events),
            'upcoming_events' => count($upcomingEvents),
            'user_project_ids' => $userProjectIds
        ]);

        return Inertia::render('Calendar', [
            'auth' => [
                'user' => $user,
            ],
            'events' => $events,
            'upcomingEvents' => $upcomingEvents,
        ]);
    }
}
