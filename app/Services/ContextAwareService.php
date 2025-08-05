<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Client;
use App\Models\Project;
use App\Models\Task;
use App\Models\Subtask;
use App\Models\Proposal;
use App\Models\DashboardWidget;
use Illuminate\Support\Collection;

class ContextAwareService
{
    private array $availableTables;
    private array $tableDescriptions;
    private array $userContext;

    public function __construct()
    {
        $this->initializeTableContext();
        $this->initializeUserContext();
    }

    /**
     * Initialize available tables and their descriptions
     */
    private function initializeTableContext(): void
    {
        $this->availableTables = [
            'users' => [
                'description' => 'User accounts and authentication',
                'columns' => ['id', 'name', 'email', 'created_at', 'updated_at'],
                'relationships' => ['clients', 'projects', 'tasks'],
                'metrics' => ['total_users', 'active_users', 'new_users_this_month']
            ],
            'clients' => [
                'description' => 'Client information and contact details',
                'columns' => ['id', 'first_name', 'last_name', 'company_name', 'email', 'phone', 'created_at'],
                'relationships' => ['projects', 'proposals', 'users'],
                'metrics' => ['total_clients', 'active_clients', 'new_clients_this_month', 'revenue_by_client']
            ],
            'projects' => [
                'description' => 'Project management and tracking',
                'columns' => ['id', 'name', 'description', 'status', 'progress', 'due_date', 'client_id', 'user_id'],
                'relationships' => ['client', 'tasks', 'user'],
                'metrics' => ['total_projects', 'active_projects', 'completed_projects', 'projects_by_status']
            ],
            'tasks' => [
                'description' => 'Individual tasks within projects',
                'columns' => ['id', 'title', 'description', 'status', 'priority', 'due_date', 'project_id', 'user_id', 'assigned_to'],
                'relationships' => ['project', 'subtasks', 'user', 'assignedUser'],
                'metrics' => ['total_tasks', 'pending_tasks', 'completed_tasks', 'tasks_by_priority', 'overdue_tasks']
            ],
            'subtasks' => [
                'description' => 'Sub-tasks within main tasks',
                'columns' => ['id', 'description', 'status', 'task_id'],
                'relationships' => ['task'],
                'metrics' => ['total_subtasks', 'completed_subtasks', 'subtasks_by_status']
            ],
            'proposals' => [
                'description' => 'Client proposals and quotes',
                'columns' => ['id', 'title', 'scope', 'price', 'status', 'client_id', 'user_id'],
                'relationships' => ['client', 'user'],
                'metrics' => ['total_proposals', 'draft_proposals', 'sent_proposals', 'accepted_proposals', 'revenue_from_proposals']
            ],
            'dashboard_widgets' => [
                'description' => 'User dashboard widget configurations',
                'columns' => ['id', 'widget_type', 'widget_key', 'title', 'description', 'configuration', 'user_id'],
                'relationships' => ['user'],
                'metrics' => ['total_widgets', 'widgets_by_type']
            ]
        ];

        $this->tableDescriptions = [
            'users' => 'User accounts and authentication system',
            'clients' => 'Client information and contact management',
            'projects' => 'Project management and progress tracking',
            'tasks' => 'Individual tasks and work items',
            'subtasks' => 'Sub-tasks within main tasks',
            'proposals' => 'Client proposals and business quotes',
            'dashboard_widgets' => 'User dashboard widget configurations'
        ];
    }

    /**
     * Initialize user-specific context
     */
    private function initializeUserContext(): void
    {
        $user = Auth::user();
        if (!$user) {
            $this->userContext = [];
            return;
        }

        $this->userContext = [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_email' => $user->email,
            'user_stats' => $this->getUserStats($user),
            'user_permissions' => $this->getUserPermissions($user),
            'recent_activity' => $this->getRecentActivity($user)
        ];
    }

    /**
     * Refresh user context (call this when user context might have changed)
     */
    public function refreshUserContext(): void
    {
        $this->initializeUserContext();
    }

    /**
     * Get user statistics
     */
    private function getUserStats(User $user): array
    {
        return [
            'total_clients' => Client::whereHas('users', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })->count(),
            'total_projects' => Project::where('user_id', $user->id)->count(),
            'total_tasks' => Task::where('user_id', $user->id)->count(),
            'assigned_tasks' => Task::where('assigned_to', $user->id)->count(),
            'total_proposals' => Proposal::where('user_id', $user->id)->count(),
            'total_widgets' => DashboardWidget::where('user_id', $user->id)->where('is_active', true)->count()
        ];
    }

    /**
     * Get user permissions (placeholder for future implementation)
     */
    private function getUserPermissions(User $user): array
    {
        return [
            'can_create_clients' => true,
            'can_create_projects' => true,
            'can_create_tasks' => true,
            'can_create_proposals' => true,
            'can_manage_widgets' => true
        ];
    }

    /**
     * Get recent user activity
     */
    private function getRecentActivity(User $user): array
    {
        return [
            'recent_projects' => Project::where('user_id', $user->id)->latest()->take(5)->get(['id', 'name', 'status'])->toArray(),
            'recent_tasks' => Task::where('user_id', $user->id)->latest()->take(5)->get(['id', 'title', 'status'])->toArray(),
            'recent_clients' => Client::whereHas('users', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })->latest()->take(5)->get(['id', 'first_name', 'last_name'])->toArray()
        ];
    }

    /**
     * Analyze user request and provide context-aware response
     */
    public function analyzeRequest(string $userPrompt, string $context = 'widget_creation'): array
    {
        $analysis = [
            'can_fulfill' => false,
            'reason' => '',
            'suggestions' => [],
            'recommendations' => [],
            'available_metrics' => [],
            'context' => []
        ];

        // Extract keywords from user prompt
        $keywords = $this->extractKeywords($userPrompt);

        // Check if the request is about school-related data
        if ($this->isSchoolRelated($keywords)) {
            $analysis['can_fulfill'] = false;
            $analysis['reason'] = 'The platform does not contain any school-related data or tables. This is a business management platform for clients, projects, tasks, and proposals.';
            $analysis['suggestions'] = [
                'Try requesting metrics related to: clients, projects, tasks, proposals, or revenue',
                'Examples: "Show me total clients", "Display pending tasks", "Track project progress"'
            ];
            $analysis['recommendations'] = [
                'Use "clients" for customer management',
                'Use "projects" for project tracking',
                'Use "tasks" for work item management',
                'Use "proposals" for business quotes'
            ];
            return $analysis;
        }

        // Analyze what metrics are available based on the request
        $availableMetrics = $this->findAvailableMetrics($keywords);

        if (empty($availableMetrics)) {
            $analysis['can_fulfill'] = false;
            $analysis['reason'] = 'No matching metrics found for your request.';
            $analysis['suggestions'] = [
                'Try using keywords like: clients, projects, tasks, proposals, revenue, progress, status',
                'Be more specific about what you want to track'
            ];
            $analysis['recommendations'] = $this->getAvailableMetricSuggestions();
            return $analysis;
        }

        // Check if user has access to the requested data
        $accessibleMetrics = $this->filterAccessibleMetrics($availableMetrics);

        if (empty($accessibleMetrics)) {
            $analysis['can_fulfill'] = false;
            $analysis['reason'] = 'You do not have access to the requested data.';
            $analysis['suggestions'] = [
                'Check your permissions or contact your administrator',
                'Try requesting data you have access to'
            ];
            return $analysis;
        }

        $analysis['can_fulfill'] = true;
        $analysis['available_metrics'] = $accessibleMetrics;
        $analysis['context'] = [
            'user_stats' => $this->userContext['user_stats'],
            'available_tables' => array_keys($this->availableTables),
            'table_descriptions' => $this->tableDescriptions
        ];

        return $analysis;
    }

    /**
     * Extract keywords from user prompt
     */
    private function extractKeywords(string $prompt): array
    {
        $prompt = strtolower($prompt);
        $keywords = [];

        // Common metric keywords with variations
        $metricKeywords = [
            'amount', 'count', 'total', 'number', 'quantity', 'sum',
            'clients', 'client', 'customers', 'customer',
            'projects', 'project', 'work',
            'tasks', 'task', 'ticket', 'tickets',
            'subtasks', 'subtask', 'sub-task', 'sub-tasks',
            'proposals', 'proposal', 'quotes', 'quote',
            'revenue', 'income', 'money', 'dollars', 'cost', 'earnings',
            'progress', 'status', 'completion', 'done', 'pending',
            'active', 'inactive', 'new', 'recent', 'overdue'
        ];

        foreach ($metricKeywords as $keyword) {
            if (str_contains($prompt, $keyword)) {
                $keywords[] = $keyword;
            }
        }

        return array_unique($keywords);
    }

    /**
     * Check if request is school-related
     */
    private function isSchoolRelated(array $keywords): bool
    {
        $schoolKeywords = ['school', 'student', 'teacher', 'class', 'grade', 'education', 'academic', 'university', 'college'];

        foreach ($keywords as $keyword) {
            if (in_array($keyword, $schoolKeywords)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Find available metrics based on keywords
     */
    private function findAvailableMetrics(array $keywords): array
    {
        $metrics = [];

        foreach ($keywords as $keyword) {
            switch ($keyword) {
                case 'clients':
                case 'client':
                case 'customers':
                case 'customer':
                    $metrics[] = [
                        'type' => 'clients',
                        'filter' => 'all',
                        'description' => 'Total number of clients',
                        'table' => 'clients'
                    ];
                    $metrics[] = [
                        'type' => 'clients',
                        'filter' => 'active',
                        'description' => 'Active clients',
                        'table' => 'clients'
                    ];
                    break;

                case 'projects':
                case 'project':
                case 'work':
                    $metrics[] = [
                        'type' => 'projects',
                        'filter' => 'all',
                        'description' => 'Total number of projects',
                        'table' => 'projects'
                    ];
                    $metrics[] = [
                        'type' => 'projects',
                        'filter' => 'active',
                        'description' => 'Active projects',
                        'table' => 'projects'
                    ];
                    break;

                case 'tasks':
                case 'task':
                case 'ticket':
                case 'tickets':
                    $metrics[] = [
                        'type' => 'tasks',
                        'filter' => 'all',
                        'description' => 'Total number of tasks',
                        'table' => 'tasks'
                    ];
                    $metrics[] = [
                        'type' => 'tasks',
                        'filter' => 'pending',
                        'description' => 'Pending tasks',
                        'table' => 'tasks'
                    ];
                    $metrics[] = [
                        'type' => 'tasks',
                        'filter' => 'completed',
                        'description' => 'Completed tasks',
                        'table' => 'tasks'
                    ];
                    break;

                case 'subtasks':
                case 'subtask':
                case 'sub-task':
                case 'sub-tasks':
                    $metrics[] = [
                        'type' => 'subtasks',
                        'filter' => 'all',
                        'description' => 'Total number of subtasks',
                        'table' => 'subtasks'
                    ];
                    $metrics[] = [
                        'type' => 'subtasks',
                        'filter' => 'assigned_to_user',
                        'description' => 'Subtasks assigned to you',
                        'table' => 'subtasks'
                    ];
                    break;

                case 'proposals':
                case 'proposal':
                case 'quotes':
                case 'quote':
                    $metrics[] = [
                        'type' => 'proposals',
                        'filter' => 'all',
                        'description' => 'Total number of proposals',
                        'table' => 'proposals'
                    ];
                    $metrics[] = [
                        'type' => 'proposals',
                        'filter' => 'draft',
                        'description' => 'Draft proposals',
                        'table' => 'proposals'
                    ];
                    $metrics[] = [
                        'type' => 'proposals',
                        'filter' => 'sent',
                        'description' => 'Sent proposals',
                        'table' => 'proposals'
                    ];
                    $metrics[] = [
                        'type' => 'proposals',
                        'filter' => 'accepted',
                        'description' => 'Accepted proposals',
                        'table' => 'proposals'
                    ];
                    break;

                case 'revenue':
                case 'income':
                case 'money':
                case 'dollars':
                case 'earnings':
                    $metrics[] = [
                        'type' => 'revenue',
                        'filter' => 'total',
                        'description' => 'Total revenue',
                        'table' => 'proposals'
                    ];
                    $metrics[] = [
                        'type' => 'revenue',
                        'filter' => 'monthly',
                        'description' => 'Monthly revenue',
                        'table' => 'proposals'
                    ];
                    break;
            }
        }

        return $metrics;
    }

    /**
     * Filter metrics based on user access
     */
    private function filterAccessibleMetrics(array $metrics): array
    {
        $user = Auth::user();
        if (!$user) {
            return [];
        }

        $accessibleMetrics = [];

        foreach ($metrics as $metric) {
            // Check if user has access to the table
            if ($this->userHasAccessToTable($user, $metric['table'])) {
                $accessibleMetrics[] = $metric;
            }
        }

        return $accessibleMetrics;
    }

    /**
     * Check if user has access to a specific table
     */
    private function userHasAccessToTable(User $user, string $table): bool
    {
        // This is a simplified check - in a real app, you'd check actual permissions
        $userAccessibleTables = [
            'clients' => true,
            'projects' => true,
            'tasks' => true,
            'subtasks' => true,
            'proposals' => true,
            'dashboard_widgets' => true
        ];

        return $userAccessibleTables[$table] ?? false;
    }

    /**
     * Get available metric suggestions
     */
    private function getAvailableMetricSuggestions(): array
    {
        return [
            'Track client metrics: "Show total clients", "Display active clients"',
            'Monitor project progress: "Show active projects", "Display project completion rate"',
            'Manage tasks: "Show pending tasks", "Display completed tasks", "Track overdue tasks"',
            'Monitor subtasks: "Show my assigned subtasks", "Display subtask completion"',
            'Track revenue: "Show total revenue", "Display monthly revenue"'
        ];
    }

    /**
     * Get platform context for AI generation
     */
    public function getPlatformContext(): array
    {
        return [
            'available_tables' => $this->availableTables,
            'table_descriptions' => $this->tableDescriptions,
            'user_context' => $this->userContext,
            'platform_description' => 'This is a business management platform for managing clients, projects, tasks, and proposals. It does not contain school-related data.',
            'supported_metrics' => [
                'clients' => ['all', 'active', 'new_this_month'],
                'projects' => ['all', 'active', 'completed', 'by_status'],
                'tasks' => ['all', 'pending', 'completed', 'overdue', 'by_priority'],
                'subtasks' => ['all', 'assigned_to_user', 'completed'],
                'proposals' => ['all', 'draft', 'sent', 'accepted'],
                'revenue' => ['total', 'monthly', 'by_client']
            ]
        ];
    }

    /**
     * Get user context for AI generation
     */
    public function getUserContext(): array
    {
        return $this->userContext;
    }

    /**
     * Validate widget configuration against platform capabilities
     */
    public function validateWidgetConfiguration(array $configuration): array
    {
        $validation = [
            'is_valid' => true,
            'errors' => [],
            'warnings' => [],
            'suggestions' => []
        ];

        $metricType = $configuration['metric_type'] ?? '';
        $metricFilter = $configuration['metric_filter'] ?? '';

        // Check if metric type is supported
        if (!array_key_exists($metricType, $this->availableTables)) {
            $validation['is_valid'] = false;
            $validation['errors'][] = "Metric type '{$metricType}' is not supported.";
            $validation['suggestions'][] = "Available metric types: " . implode(', ', array_keys($this->availableTables));
        }

        // Check if metric filter is valid for the type
        if ($metricType && $metricFilter) {
            $supportedFilters = $this->getSupportedFilters($metricType);
            if (!in_array($metricFilter, $supportedFilters)) {
                $validation['warnings'][] = "Filter '{$metricFilter}' may not be optimal for '{$metricType}'.";
                $validation['suggestions'][] = "Consider using: " . implode(', ', $supportedFilters);
            }
        }

        return $validation;
    }

    /**
     * Get supported filters for a metric type
     */
    private function getSupportedFilters(string $metricType): array
    {
        $filters = [
            'clients' => ['all', 'active', 'new_this_month'],
            'projects' => ['all', 'active', 'completed', 'by_status'],
            'tasks' => ['all', 'pending', 'completed', 'overdue', 'by_priority'],
            'subtasks' => ['all', 'assigned_to_user', 'completed'],
            'proposals' => ['all', 'draft', 'sent', 'accepted'],
            'revenue' => ['total', 'monthly', 'by_client']
        ];

        return $filters[$metricType] ?? [];
    }
}
