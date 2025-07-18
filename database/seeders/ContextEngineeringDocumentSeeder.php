<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ContextEngineeringDocument;
use App\Models\DevProject;
use App\Models\User;

class ContextEngineeringDocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create a user
        $user = User::first();
        if (!$user) {
            $user = User::factory()->create();
        }

        // Get or create dev projects
        $projects = DevProject::all();
        if ($projects->isEmpty()) {
            // Create some sample dev projects
            $projects = collect([
                DevProject::create([
                    'title' => 'E-commerce Platform',
                    'description' => 'A modern e-commerce platform with user authentication, product catalog, shopping cart, and payment processing.',
                    'is_generated' => false,
                    'status' => 'active',
                    'created_by' => $user->id,
                    'updated_by' => $user->id
                ]),
                DevProject::create([
                    'title' => 'Task Management App',
                    'description' => 'A collaborative task management application with real-time updates, team collaboration, and progress tracking.',
                    'is_generated' => false,
                    'status' => 'active',
                    'created_by' => $user->id,
                    'updated_by' => $user->id
                ]),
                DevProject::create([
                    'title' => 'Blog Platform',
                    'description' => 'A content management system for blogs with rich text editing, categories, tags, and SEO optimization.',
                    'is_generated' => false,
                    'status' => 'active',
                    'created_by' => $user->id,
                    'updated_by' => $user->id
                ])
            ]);
        }

        $types = [
            'implementation' => 'Implementation Plan',
            'workflow' => 'Workflow Documentation',
            'project_structure' => 'Project Structure',
            'ui_ux' => 'UI/UX Guidelines',
            'bug_tracking' => 'Bug Tracking System',
            'custom' => 'Custom Documentation'
        ];

        foreach ($projects as $project) {
            // Implementation Plan
            ContextEngineeringDocument::create([
                'dev_project_id' => $project->id,
                'name' => $project->title . ' - Implementation Plan',
                'description' => 'Comprehensive implementation plan for ' . $project->title,
                'type' => 'implementation',
                'content' => "# Implementation Plan: {$project->title}\n\n## Overview\nThis document outlines the implementation strategy for {$project->title}.\n\n## Phase 1: Foundation\n- Set up development environment\n- Initialize project structure\n- Configure version control\n- Set up CI/CD pipeline\n\n## Phase 2: Core Development\n- Implement core features\n- Database design and implementation\n- API development\n- Frontend development\n\n## Phase 3: Testing & Deployment\n- Unit testing\n- Integration testing\n- User acceptance testing\n- Production deployment\n\n## Timeline\n- Phase 1: 2 weeks\n- Phase 2: 8 weeks\n- Phase 3: 2 weeks\n\n## Resources\n- Development team: 4 developers\n- Design team: 2 designers\n- QA team: 2 testers",
                'is_generated' => false,
                'is_template' => false,
                'is_active' => true,
                'version' => 1,
                'created_by' => $user->id,
                'updated_by' => $user->id
            ]);

            // Workflow Documentation
            ContextEngineeringDocument::create([
                'dev_project_id' => $project->id,
                'name' => $project->title . ' - Workflow Documentation',
                'description' => 'Detailed workflow documentation for ' . $project->title,
                'type' => 'workflow',
                'content' => "# Workflow Documentation: {$project->title}\n\n## Development Workflow\n\n### 1. Feature Development\n1. Create feature branch from main\n2. Implement feature with tests\n3. Create pull request\n4. Code review process\n5. Merge to main branch\n\n### 2. Release Process\n1. Create release branch\n2. Version bump and changelog\n3. Final testing\n4. Deploy to staging\n5. Deploy to production\n\n### 3. Bug Fix Process\n1. Create bug report\n2. Assign to developer\n3. Fix and test\n4. Create hotfix branch\n5. Deploy fix\n\n## Git Workflow\n- main: Production-ready code\n- develop: Integration branch\n- feature/*: New features\n- hotfix/*: Critical fixes\n- release/*: Release preparation",
                'is_generated' => false,
                'is_template' => false,
                'is_active' => true,
                'version' => 1,
                'created_by' => $user->id,
                'updated_by' => $user->id
            ]);

            // Project Structure
            ContextEngineeringDocument::create([
                'dev_project_id' => $project->id,
                'name' => $project->title . ' - Project Structure',
                'description' => 'Project structure and organization for ' . $project->title,
                'type' => 'project_structure',
                'content' => "# Project Structure: {$project->title}\n\n## Directory Structure\n```\nproject-root/\n├── app/\n│   ├── Http/\n│   │   ├── Controllers/\n│   │   ├── Middleware/\n│   │   └── Requests/\n│   ├── Models/\n│   ├── Services/\n│   └── Providers/\n├── config/\n├── database/\n│   ├── migrations/\n│   ├── seeders/\n│   └── factories/\n├── public/\n├── resources/\n│   ├── js/\n│   ├── css/\n│   └── views/\n├── routes/\n├── storage/\n└── tests/\n```\n\n## Key Components\n- **Controllers**: Handle HTTP requests\n- **Models**: Database interactions\n- **Services**: Business logic\n- **Middleware**: Request processing\n- **Views**: Frontend templates\n\n## Configuration\n- Environment variables in .env\n- Database configuration\n- Cache configuration\n- Queue configuration",
                'is_generated' => false,
                'is_template' => false,
                'is_active' => true,
                'version' => 1,
                'created_by' => $user->id,
                'updated_by' => $user->id
            ]);

            // UI/UX Guidelines
            ContextEngineeringDocument::create([
                'dev_project_id' => $project->id,
                'name' => $project->title . ' - UI/UX Guidelines',
                'description' => 'UI/UX design guidelines for ' . $project->title,
                'type' => 'ui_ux',
                'content' => "# UI/UX Guidelines: {$project->title}\n\n## Design Principles\n- **Simplicity**: Clean, uncluttered interfaces\n- **Consistency**: Uniform design patterns\n- **Accessibility**: WCAG 2.1 AA compliance\n- **Responsiveness**: Mobile-first design\n\n## Color Palette\n- Primary: #3B82F6 (Blue)\n- Secondary: #10B981 (Green)\n- Accent: #F59E0B (Yellow)\n- Neutral: #6B7280 (Gray)\n- Success: #10B981 (Green)\n- Error: #EF4444 (Red)\n- Warning: #F59E0B (Yellow)\n\n## Typography\n- **Headings**: Inter, sans-serif\n- **Body**: Inter, sans-serif\n- **Monospace**: JetBrains Mono\n\n## Spacing System\n- 4px base unit\n- 8px, 16px, 24px, 32px, 48px, 64px\n\n## Component Guidelines\n- Buttons: 8px padding, 4px border radius\n- Cards: 16px padding, 8px border radius\n- Forms: 12px spacing between elements",
                'is_generated' => false,
                'is_template' => false,
                'is_active' => true,
                'version' => 1,
                'created_by' => $user->id,
                'updated_by' => $user->id
            ]);

            // Bug Tracking System
            ContextEngineeringDocument::create([
                'dev_project_id' => $project->id,
                'name' => $project->title . ' - Bug Tracking System',
                'description' => 'Bug tracking and issue management for ' . $project->title,
                'type' => 'bug_tracking',
                'content' => "# Bug Tracking System: {$project->title}\n\n## Issue Categories\n- **Bug**: Software defect\n- **Feature**: New functionality request\n- **Enhancement**: Improvement to existing feature\n- **Task**: General development task\n- **Documentation**: Documentation updates\n\n## Priority Levels\n- **Critical**: System down, data loss\n- **High**: Major functionality broken\n- **Medium**: Minor functionality issues\n- **Low**: Cosmetic issues, nice-to-have\n\n## Status Workflow\n1. **Open**: Issue reported\n2. **In Progress**: Being worked on\n3. **Review**: Ready for testing\n4. **Testing**: Under QA review\n5. **Resolved**: Fixed and verified\n6. **Closed**: Completed\n\n## Bug Report Template\n```\n**Title**: Brief description\n**Description**: Detailed explanation\n**Steps to Reproduce**:\n1. Step 1\n2. Step 2\n3. Step 3\n**Expected Result**: What should happen\n**Actual Result**: What actually happens\n**Environment**: OS, browser, version\n**Screenshots**: If applicable\n```",
                'is_generated' => false,
                'is_template' => false,
                'is_active' => true,
                'version' => 1,
                'created_by' => $user->id,
                'updated_by' => $user->id
            ]);
        }

        // Create some template documents
        ContextEngineeringDocument::create([
            'dev_project_id' => $projects->first()->id,
            'name' => 'API Documentation Template',
            'description' => 'Template for API documentation',
            'type' => 'custom',
            'content' => "# API Documentation Template\n\n## Endpoint: {endpoint_name}\n\n### Description\n{endpoint_description}\n\n### Method\n{http_method}\n\n### URL\n`{base_url}/{endpoint_path}`\n\n### Parameters\n| Parameter | Type | Required | Description |\n|-----------|------|----------|-------------|\n| {param_name} | {param_type} | {required} | {description} |\n\n### Request Example\n```json\n{\n  \"key\": \"value\"\n}\n```\n\n### Response Example\n```json\n{\n  \"status\": \"success\",\n  \"data\": {}\n}\n```\n\n### Error Codes\n| Code | Description |\n|------|-------------|\n| 400 | Bad Request |\n| 401 | Unauthorized |\n| 404 | Not Found |\n| 500 | Internal Server Error |",
            'is_generated' => false,
            'is_template' => true,
            'is_active' => true,
            'version' => 1,
            'variables' => [
                'endpoint_name' => 'string',
                'endpoint_description' => 'string',
                'http_method' => 'string',
                'base_url' => 'string',
                'endpoint_path' => 'string',
                'param_name' => 'string',
                'param_type' => 'string',
                'required' => 'boolean',
                'description' => 'string'
            ],
            'created_by' => $user->id,
            'updated_by' => $user->id
        ]);
    }
}
