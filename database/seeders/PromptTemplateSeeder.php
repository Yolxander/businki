<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PromptTemplate;

class PromptTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Default Proposal',
                'type' => 'proposal',
                'description' => 'Professional proposal template with variables',
                'template' => <<<EOT
Create a professional proposal based on the following client intake information:

Client: {full_name}
Company: {company_name}
Project Description: {project_description}
Budget Range: {budget_range}
Deadline: {deadline}
Project Type: {project_type}
Project Examples: {project_examples}

Requirements:
- Create a compelling proposal title
- Write a detailed project scope
- Include specific deliverables if available
- Create a timeline with phases if available
- Include pricing for each phase if available
- Ensure the total price fits within the budget range
- Make the proposal professional and client-ready
EOT,
                'is_active' => true,
            ],
            [
                'name' => 'Default Project',
                'type' => 'project',
                'description' => 'Project structure template with variables',
                'template' => <<<EOT
Create a project structure based on the following proposal:

Proposal Title: {title}
Scope: {scope}
Deliverables: {deliverables}
Total Price: {price}
Timeline: {timeline}

Requirements:
- Create a project title
- Set appropriate kickoff and delivery dates
- Determine current phase
- Add relevant project notes
EOT,
                'is_active' => true,
            ],
            [
                'name' => 'Default Personal Project',
                'type' => 'personal_project',
                'description' => 'Personal project structure template',
                'template' => <<<EOT
Create a personal project structure based on the following information:

Project Type: {project_type}
Description: {description}
Include in Portfolio: {include_in_portfolio}

Requirements:
- Create a compelling project title
- Set realistic kickoff and delivery dates
- Determine appropriate project phase
- Add relevant project notes
- Make it suitable for portfolio if requested
EOT,
                'is_active' => true,
            ],
            [
                'name' => 'Default Tasks',
                'type' => 'task',
                'description' => 'Tasks generation template with variables',
                'template' => <<<EOT
Create up to {max_tasks} tasks for the following project:

Project Title: {project_title}
Project Description: {project_description}
Project Scope: {project_scope}
Timeline: {timeline}

Requirements:
- Create practical, actionable tasks
- Include appropriate priorities (low, medium, high)
- Provide realistic time estimates
- Add relevant tags
- Include subtasks for complex tasks if available
EOT,
                'is_active' => true,
            ],
            [
                'name' => 'Default Personal Tasks',
                'type' => 'personal_task',
                'description' => 'Personal project tasks template',
                'template' => <<<EOT
Create tasks for the following personal project:

Project Type: {project_type}
Project Title: {project_title}
Description: {description}

Requirements:
- Create practical, actionable tasks
- Include appropriate priorities
- Provide realistic time estimates
- Add relevant tags
- Make tasks suitable for personal project management
EOT,
                'is_active' => true,
            ],
        ];

        foreach ($templates as $tpl) {
            PromptTemplate::updateOrCreate([
                'name' => $tpl['name'],
                'type' => $tpl['type'],
            ], $tpl);
        }
    }
}
