<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\DashboardWidget;

class DashboardWidgetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users
        $users = User::all();

        foreach ($users as $user) {
            $this->createDefaultWidgets($user);
        }
    }

    /**
     * Create default widgets for a user
     */
    private function createDefaultWidgets(User $user): void
    {
        $widgets = [
            // Quick Stats Widgets (4 cards)
            [
                'widget_type' => 'quick_stats',
                'widget_key' => 'quick_stat_active_projects',
                'title' => 'Active Projects',
                'description' => 'Projects in progress',
                'configuration' => [
                    'metric_type' => 'projects',
                    'metric_filter' => 'active',
                    'icon' => 'Briefcase',
                    'trend' => '+3 this week',
                    'show_trend' => true,
                    'refresh_interval' => 300,
                ],
                'position' => 1,
            ],
            [
                'widget_type' => 'quick_stats',
                'widget_key' => 'quick_stat_total_clients',
                'title' => 'Total Clients',
                'description' => 'Active clients',
                'configuration' => [
                    'metric_type' => 'clients',
                    'metric_filter' => 'all',
                    'icon' => 'Users',
                    'trend' => '+2 this month',
                    'show_trend' => true,
                    'refresh_interval' => 300,
                ],
                'position' => 2,
            ],
            [
                'widget_type' => 'quick_stats',
                'widget_key' => 'quick_stat_subtasks',
                'title' => 'Subtask Tracker',
                'description' => 'This widget displays the total number of subtasks assigned to you, helping you manage your workload effectively.',
                'configuration' => [
                    'metric_type' => 'subtasks',
                    'metric_filter' => 'assigned_to_user',
                    'icon' => 'Checklist',
                    'trend' => '+5% from last month',
                    'show_trend' => true,
                    'refresh_interval' => 300,
                ],
                'position' => 4,
            ],

            // Recent Tasks Widget
            [
                'widget_type' => 'recent_tasks',
                'widget_key' => 'recent_tasks_widget',
                'title' => 'Recent Tasks',
                'description' => 'Latest task updates',
                'configuration' => [
                    'max_items' => 5,
                    'show_priority' => true,
                    'show_due_date' => true,
                    'show_project' => true,
                    'show_status' => true,
                    'refresh_interval' => 60,
                    'show_zen_mode_button' => true,
                ],
                'position' => 5,
            ],

            // Recent Projects Widget
            [
                'widget_type' => 'recent_projects',
                'widget_key' => 'recent_projects_widget',
                'title' => 'Recent Projects',
                'description' => 'Your active and recent projects',
                'configuration' => [
                    'max_items' => 5,
                    'show_progress' => true,
                    'show_client' => true,
                    'show_status' => true,
                    'show_due_date' => true,
                    'refresh_interval' => 120,
                ],
                'position' => 6,
            ],

            // Quick Actions Widget
            [
                'widget_type' => 'quick_actions',
                'widget_key' => 'quick_actions_widget',
                'title' => 'Quick Actions',
                'description' => 'Get things done faster',
                'configuration' => [
                    'actions' => [
                        'create_client' => [
                            'enabled' => true,
                            'icon' => 'Users',
                            'label' => 'Add New Client',
                            'href' => '/clients/create',
                        ],
                        'create_project' => [
                            'enabled' => true,
                            'icon' => 'FileText',
                            'label' => 'Create Project',
                            'href' => null, // Opens modal
                        ],
                        'create_task' => [
                            'enabled' => true,
                            'icon' => 'Target',
                            'label' => 'Add Task',
                            'href' => '/tasks/create',
                        ],
                        'zen_mode' => [
                            'enabled' => true,
                            'icon' => 'Zap',
                            'label' => 'Zen Mode',
                            'href' => '/zen-mode',
                        ],
                        'ai_playground' => [
                            'enabled' => true,
                            'icon' => 'Brain',
                            'label' => 'AI Playground',
                            'href' => '/playground',
                        ],
                    ],
                ],
                'position' => 7,
            ],

            // Recent Proposals Widget
            [
                'widget_type' => 'recent_proposals',
                'widget_key' => 'recent_proposals_widget',
                'title' => 'Recent Proposals',
                'description' => 'Latest client proposals',
                'configuration' => [
                    'max_items' => 5,
                    'show_status' => true,
                    'show_client' => true,
                    'show_created_date' => true,
                    'refresh_interval' => 300,
                ],
                'position' => 8,
            ],
        ];

        foreach ($widgets as $widgetData) {
            DashboardWidget::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'widget_type' => $widgetData['widget_type'],
                    'widget_key' => $widgetData['widget_key'],
                ],
                [
                    'title' => $widgetData['title'],
                    'description' => $widgetData['description'],
                    'configuration' => $widgetData['configuration'],
                    'is_ai_generated' => false,
                    'is_active' => true,
                    'position' => $widgetData['position'],
                ]
            );
        }
    }
}
