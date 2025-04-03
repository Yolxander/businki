<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index()
    {
        try {
            $projects = DB::table('projects')
                ->select(
                    'projects.*',
                    'providers.full_name as provider_name',
                    'providers.email as provider_email',
                    'provider_types.name as provider_type',
                    'auth.users.email as user_email'
                )
                ->leftJoin('providers', function ($join) {
                    $join->on('projects.provider_id', '=', 'providers.id')
                        ->whereRaw("LENGTH(providers.id::text) = 36");
                })
                ->leftJoin('provider_types', 'providers.provider_type_id', '=', 'provider_types.id')
                ->leftJoin('auth.users', 'providers.id', '=', 'auth.users.id')
                ->whereRaw("LENGTH(projects.id::text) = 36")
                ->whereRaw("LENGTH(projects.provider_id::text) = 36")
                ->get();

            foreach ($projects as $project) {
                // Attach tasks and subtasks
                $project->tasks = DB::table('tasks')
                    ->where('project_id', $project->id)
                    ->whereRaw("LENGTH(project_id::text) = 36")
                    ->get()
                    ->map(function ($task) {
                        $task->subtasks = DB::table('subtasks')
                            ->where('task_id', $task->id)
                            ->whereRaw("LENGTH(task_id::text) = 36")
                            ->get();
                        return $task;
                    });

                // Attach timeline events
                $project->timeline = DB::table('project_timeline_events')
                    ->where('project_id', $project->id)
                    ->orderBy('event_date')
                    ->get();
            }

            return response()->json([
                'success' => true,
                'data' => $projects
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching projects: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error fetching project data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getByClient(Request $request)
    {
        $clientId = $request->input('client_id');

        if (!$clientId || strlen($clientId) !== 36) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or missing client_id. Must be a valid UUID (36 characters).'
            ], 400);
        }

        try {
            $projects = DB::table('projects')
                ->select(
                    'projects.*',
                    'providers.full_name as provider_name',
                    'providers.email as provider_email',
                    'provider_types.name as provider_type',
                    'auth.users.email as user_email'
                )
                ->leftJoin('providers', function ($join) {
                    $join->on('projects.provider_id', '=', 'providers.id')
                        ->whereRaw("LENGTH(providers.id::text) = 36");
                })
                ->leftJoin('provider_types', 'providers.provider_type_id', '=', 'provider_types.id')
                ->leftJoin('auth.users', 'providers.id', '=', 'auth.users.id')
                ->where('projects.client_id', $clientId)
                ->whereRaw("LENGTH(projects.id::text) = 36")
                ->whereRaw("LENGTH(projects.provider_id::text) = 36")
                ->get();

            foreach ($projects as $project) {
                $project->tasks = DB::table('tasks')
                    ->where('project_id', $project->id)
                    ->whereRaw("LENGTH(project_id::text) = 36")
                    ->get()
                    ->map(function ($task) {
                        $task->subtasks = DB::table('subtasks')
                            ->where('task_id', $task->id)
                            ->whereRaw("LENGTH(task_id::text) = 36")
                            ->get();
                        return $task;
                    });

                // Attach timeline events
                $project->timeline = DB::table('project_timeline_events')
                    ->where('project_id', $project->id)
                    ->orderBy('event_date')
                    ->get();
            }

            return response()->json([
                'success' => true,
                'data' => $projects
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching projects for client: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error fetching project data by client',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function allProjects()
    {
        $projects = Project::with([
            'tasks.subtasks'
        ])->get();

        return response()->json([
            'projects' => $projects
        ]);
    }
}
