<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Prompt;

class PromptSeeder extends Seeder
{
    public function run(): void
    {
        Prompt::create([
            'title' => 'Client Email Follow-up',
            'description' => 'A polite follow-up email template for clients after a meeting.',
            'content' => "Hi [Client],\nJust following up on our recent meeting... [rest of prompt]",
            'tags' => ['Client Emails', 'Communication'],
            'context' => 'Project: Acme Website',
            'favorite' => true,
        ]);
        Prompt::create([
            'title' => 'UX Audit Checklist',
            'description' => 'Checklist for auditing a website UX.',
            'content' => 'Review navigation, check mobile responsiveness, ...',
            'tags' => ['UX Audit', 'Design'],
            'context' => 'General',
            'favorite' => false,
        ]);
        Prompt::create([
            'title' => 'Creative Brainstorm',
            'description' => 'Prompt for generating creative ideas for branding.',
            'content' => 'Generate 10 unique branding ideas for a new eco-friendly product.',
            'tags' => ['Brainstorm', 'Copywriting'],
            'context' => 'Task: Logo Design',
            'favorite' => false,
        ]);
    }
}
