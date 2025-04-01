<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
                $project->tasks = DB::table('tasks')
                    ->where('project_id', $project->id)
                    ->whereRaw("LENGTH(project_id::text) = 36")
                    ->select('*')
                    ->get()
                    ->map(function ($task) {
                        $task->subtasks = DB::table('subtasks')
                            ->where('task_id', $task->id)
                            ->whereRaw("LENGTH(task_id::text) = 36")
                            ->get();
                        return $task;
                    });
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
}
