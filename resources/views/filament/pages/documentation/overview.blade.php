<x-filament::card class="dark:bg-gray-900 dark:text-gray-100">
    <div class="prose max-w-none dark:prose-invert">
        <h2 class="text-xl font-semibold mb-4">AI Generation System Overview</h2>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div>
                <h3 class="text-lg font-medium mb-2">How It Works</h3>
                <p class="mb-4">
                    The AI generation system uses OpenAI's GPT models to automatically create content for proposals,
                    projects, tasks, and other business documents. The system processes your prompts and generates
                    contextually relevant content based on the selected parameters.
                </p>

                <h3 class="text-lg font-medium mb-2">Generation Types</h3>
                <ul class="list-disc pl-6 mb-4">
                    <li><strong>Proposal:</strong> Creates detailed project proposals with scope, timeline, and pricing</li>
                    <li><strong>Project:</strong> Generates project plans with tasks, milestones, and deliverables</li>
                    <li><strong>Task:</strong> Creates individual task descriptions with requirements and acceptance criteria</li>
                    <li><strong>Subtask:</strong> Breaks down complex tasks into smaller, manageable components</li>
                    <li><strong>Service:</strong> Generates service descriptions and feature lists</li>
                    <li><strong>Package:</strong> Creates package offerings with pricing tiers and features</li>
                </ul>
            </div>
            <div>
                <h3 class="text-lg font-medium mb-2">Parameters Explained</h3>
                <ul class="list-disc pl-6 mb-4">
                    <li><strong>Model:</strong> Choose between GPT-4 (most capable), GPT-4 Turbo (faster), or GPT-3.5 Turbo (most economical)</li>
                    <li><strong>Temperature:</strong> Controls randomness (0 = deterministic, 2 = very creative)</li>
                    <li><strong>Max Tokens:</strong> Maximum length of the generated response</li>
                </ul>

                <h3 class="text-lg font-medium mb-2">Current Use Cases</h3>
                <p class="mb-2">
                    The AI generation system is currently integrated across the following areas:
                </p>
                <ul class="list-disc pl-6">
                    <li>Automatic proposal generation from client intake forms</li>
                    <li>Project planning and task breakdown</li>
                    <li>Service and package description creation</li>
                    <li>Content optimization and enhancement</li>
                </ul>
            </div>
        </div>
    </div>
</x-filament::card>
