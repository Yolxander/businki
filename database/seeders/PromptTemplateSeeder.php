<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PromptTemplate;

class PromptTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            // Business Templates
            [
                'name' => 'Professional Proposal Generator',
                'type' => 'proposal',
                'description' => 'Generate comprehensive business proposals with pricing and timelines',
                'template' => <<<EOT
Create a professional business proposal based on the following client information:

Client: {full_name}
Company: {company_name}
Project Description: {project_description}
Budget Range: {budget_range}
Deadline: {deadline}
Project Type: {project_type}
Project Examples: {project_examples}

Requirements:
- Create a compelling proposal title
- Write a detailed project scope with clear objectives
- Include specific deliverables with descriptions
- Create a timeline with phases and milestones
- Include pricing for each phase with justification
- Ensure the total price fits within the budget range
- Add terms and conditions
- Include next steps and call-to-action
- Make the proposal professional and client-ready
EOT,
                'is_active' => true,
            ],
            [
                'name' => 'Project Structure Creator',
                'type' => 'project',
                'description' => 'Create detailed project structures from proposal data',
                'template' => <<<EOT
Create a comprehensive project structure based on the following proposal:

Proposal Title: {title}
Scope: {scope}
Deliverables: {deliverables}
Total Price: {price}
Timeline: {timeline}

Requirements:
- Create a compelling project title
- Set realistic kickoff and delivery dates
- Determine current project phase (Planning, Development, Testing, Launch)
- Add detailed project notes and requirements
- Include risk assessment and mitigation strategies
- Define success metrics and KPIs
- Outline communication and reporting structure
EOT,
                'is_active' => true,
            ],
            [
                'name' => 'Task Breakdown Generator',
                'type' => 'task',
                'description' => 'Break down projects into actionable tasks with priorities',
                'template' => <<<EOT
Create up to {max_tasks} detailed tasks for the following project:

Project Title: {project_title}
Project Description: {project_description}
Project Scope: {project_scope}
Timeline: {timeline}

Requirements:
- Create practical, actionable tasks with clear descriptions
- Include appropriate priorities (low, medium, high, critical)
- Provide realistic time estimates in hours
- Add relevant tags for categorization
- Include dependencies between tasks
- Specify required resources and skills
- Add acceptance criteria for each task
- Include subtasks for complex tasks
EOT,
                'is_active' => true,
            ],

            // Content Creation Templates
            [
                'name' => 'Blog Post Writer',
                'type' => 'content',
                'description' => 'Generate engaging blog posts on any topic',
                'template' => <<<EOT
Write a comprehensive blog post about: {topic}

Target Audience: {audience}
Tone: {tone}
Word Count: {word_count}
Keywords: {keywords}

Requirements:
- Create an attention-grabbing headline
- Write an engaging introduction that hooks the reader
- Structure the content with clear headings and subheadings
- Include relevant examples and case studies
- Add actionable tips and insights
- Write a compelling conclusion with call-to-action
- Optimize for SEO with natural keyword integration
- Make it engaging and easy to read
EOT,
                'is_active' => true,
            ],
            [
                'name' => 'Social Media Content Creator',
                'type' => 'content',
                'description' => 'Create engaging social media posts and captions',
                'template' => <<<EOT
Create social media content for: {platform}

Topic: {topic}
Brand Voice: {brand_voice}
Target Audience: {audience}
Call-to-Action: {cta}

Requirements:
- Create multiple post variations (3-5 posts)
- Include relevant hashtags
- Write engaging captions that encourage interaction
- Add emojis and formatting for visual appeal
- Include questions to encourage engagement
- Optimize for the specific platform's best practices
- Keep content authentic and brand-consistent
EOT,
                'is_active' => true,
            ],
            [
                'name' => 'Email Newsletter Writer',
                'type' => 'content',
                'description' => 'Create compelling email newsletters',
                'template' => <<<EOT
Write an email newsletter with the following details:

Subject: {subject}
Main Topic: {topic}
Target Audience: {audience}
Newsletter Type: {type} (promotional, educational, update, etc.)

Requirements:
- Write a compelling subject line
- Create an engaging opening paragraph
- Include 2-3 main content sections
- Add relevant links and resources
- Include a clear call-to-action
- Write a professional signature
- Keep it concise and scannable
- Add personal touches and storytelling
EOT,
                'is_active' => true,
            ],

            // Development Templates
            [
                'name' => 'Code Review Assistant',
                'type' => 'code',
                'description' => 'Review and improve code quality',
                'template' => <<<EOT
Review the following code and provide feedback:

Language: {language}
Code:
{code}

Requirements:
- Identify potential bugs and issues
- Suggest performance improvements
- Check for security vulnerabilities
- Recommend code style improvements
- Suggest better practices and patterns
- Provide specific examples of improvements
- Rate the overall code quality (1-10)
- Give actionable recommendations
EOT,
                'is_active' => true,
            ],
            [
                'name' => 'API Documentation Generator',
                'type' => 'code',
                'description' => 'Generate comprehensive API documentation',
                'template' => <<<EOT
Create API documentation for the following endpoint:

Endpoint: {endpoint}
Method: {method}
Description: {description}
Parameters: {parameters}
Response: {response}

Requirements:
- Write a clear endpoint description
- Document all parameters with types and examples
- Include request/response examples
- Add error handling documentation
- Include authentication requirements
- Provide usage examples in multiple languages
- Add rate limiting information
- Include troubleshooting tips
EOT,
                'is_active' => true,
            ],
            [
                'name' => 'Database Query Optimizer',
                'type' => 'code',
                'description' => 'Optimize database queries for better performance',
                'template' => <<<EOT
Optimize the following database query:

Database: {database_type}
Query:
{query}

Current Performance Issues: {issues}

Requirements:
- Analyze the query performance
- Suggest indexing strategies
- Recommend query structure improvements
- Identify potential bottlenecks
- Provide optimized query versions
- Explain the optimization reasoning
- Include performance metrics
- Suggest monitoring approaches
EOT,
                'is_active' => true,
            ],

            // Creative Templates
            [
                'name' => 'Creative Story Writer',
                'type' => 'creative',
                'description' => 'Generate creative stories and narratives',
                'template' => <<<EOT
Write a creative story with the following elements:

Genre: {genre}
Setting: {setting}
Main Character: {character}
Conflict: {conflict}
Theme: {theme}
Length: {length}

Requirements:
- Create an engaging opening scene
- Develop compelling characters
- Build tension and conflict
- Include vivid descriptions
- Create a satisfying resolution
- Use appropriate pacing
- Include dialogue and action
- Maintain consistent tone and style
EOT,
                'is_active' => true,
            ],
            [
                'name' => 'Marketing Copywriter',
                'type' => 'creative',
                'description' => 'Create persuasive marketing copy',
                'template' => <<<EOT
Create marketing copy for the following product/service:

Product: {product}
Target Audience: {audience}
Value Proposition: {value_prop}
Tone: {tone}
Format: {format} (ad, landing page, email, etc.)

Requirements:
- Write compelling headlines and subheadings
- Highlight key benefits and features
- Address pain points and objections
- Include social proof elements
- Create urgency and scarcity
- Add clear call-to-action
- Use persuasive language techniques
- Optimize for conversion
EOT,
                'is_active' => true,
            ],

            // Analysis Templates
            [
                'name' => 'Data Analysis Report',
                'type' => 'analysis',
                'description' => 'Analyze data and create comprehensive reports',
                'template' => <<<EOT
Analyze the following data and create a report:

Data Type: {data_type}
Time Period: {time_period}
Key Metrics: {metrics}
Business Context: {context}

Requirements:
- Provide executive summary
- Identify key trends and patterns
- Calculate relevant statistics
- Create actionable insights
- Recommend next steps
- Include visual descriptions
- Address potential limitations
- Suggest follow-up analysis
EOT,
                'is_active' => true,
            ],
            [
                'name' => 'Competitive Analysis',
                'type' => 'analysis',
                'description' => 'Analyze competitors and market positioning',
                'template' => <<<EOT
Conduct a competitive analysis for:

Product/Service: {product}
Competitors: {competitors}
Market: {market}
Analysis Focus: {focus}

Requirements:
- Analyze competitor strengths and weaknesses
- Compare pricing strategies
- Evaluate market positioning
- Identify competitive advantages
- Assess market opportunities
- Recommend strategic actions
- Include SWOT analysis
- Provide actionable insights
EOT,
                'is_active' => true,
            ],

            // Personal Development Templates
            [
                'name' => 'Personal Project Planner',
                'type' => 'personal_project',
                'description' => 'Plan and structure personal projects',
                'template' => <<<EOT
Create a personal project plan for:

Project Type: {project_type}
Description: {description}
Goals: {goals}
Timeline: {timeline}
Include in Portfolio: {include_in_portfolio}

Requirements:
- Create a compelling project title
- Set realistic milestones and deadlines
- Define clear objectives and success criteria
- Plan resource requirements
- Identify potential challenges
- Create action steps and tasks
- Include learning objectives
- Plan for portfolio presentation if needed
EOT,
                'is_active' => true,
            ],
            [
                'name' => 'Learning Path Designer',
                'type' => 'personal_task',
                'description' => 'Design structured learning paths and courses',
                'template' => <<<EOT
Design a learning path for:

Skill/Topic: {skill}
Current Level: {current_level}
Target Level: {target_level}
Time Available: {time_available}
Learning Style: {learning_style}

Requirements:
- Create a structured learning roadmap
- Break down into manageable modules
- Include practical exercises and projects
- Recommend learning resources
- Set realistic milestones
- Include assessment methods
- Plan for skill application
- Create a timeline for completion
EOT,
                'is_active' => true,
            ],

            // Testing Templates
            [
                'name' => 'User Story Generator',
                'type' => 'testing',
                'description' => 'Generate user stories for software development',
                'template' => <<<EOT
Create user stories for the following feature:

Feature: {feature}
User Type: {user_type}
Business Value: {business_value}
Acceptance Criteria: {acceptance_criteria}

Requirements:
- Write user stories in "As a... I want... So that..." format
- Include detailed acceptance criteria
- Add story points estimation
- Define definition of done
- Include edge cases and scenarios
- Add technical considerations
- Include testing requirements
- Plan for user acceptance testing
EOT,
                'is_active' => true,
            ],
            [
                'name' => 'Test Case Designer',
                'type' => 'testing',
                'description' => 'Design comprehensive test cases',
                'template' => <<<EOT
Design test cases for the following functionality:

Feature: {feature}
Requirements: {requirements}
Test Environment: {environment}
Test Types: {test_types}

Requirements:
- Create positive and negative test cases
- Include boundary value analysis
- Add edge case scenarios
- Define test data requirements
- Include expected results
- Plan test execution steps
- Add risk assessment
- Include regression testing considerations
EOT,
                'is_active' => true,
            ],

            // Communication Templates
            [
                'name' => 'Meeting Agenda Creator',
                'type' => 'communication',
                'description' => 'Create structured meeting agendas',
                'template' => <<<EOT
Create a meeting agenda for:

Meeting Type: {meeting_type}
Duration: {duration}
Participants: {participants}
Objectives: {objectives}

Requirements:
- Create a clear meeting title and purpose
- List specific agenda items with time allocations
- Include participant roles and responsibilities
- Add discussion topics and questions
- Plan for decision-making points
- Include action item tracking
- Add follow-up and next steps
- Consider meeting logistics
EOT,
                'is_active' => true,
            ],
            [
                'name' => 'Presentation Outline',
                'type' => 'communication',
                'description' => 'Create presentation outlines and structures',
                'template' => <<<EOT
Create a presentation outline for:

Topic: {topic}
Audience: {audience}
Duration: {duration}
Purpose: {purpose}

Requirements:
- Create a compelling opening hook
- Structure main points logically
- Include supporting evidence and examples
- Plan for audience engagement
- Add visual element suggestions
- Include call-to-action
- Plan for Q&A session
- Create memorable closing
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
