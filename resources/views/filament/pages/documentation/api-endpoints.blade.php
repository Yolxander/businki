<x-filament::card class="dark:bg-gray-900 dark:text-gray-100">
    <div class="prose max-w-none dark:prose-invert">
        <h2 class="text-xl font-semibold mb-4">AI Generation API Endpoints</h2>

        <div class="space-y-6">
            <!-- Generate Proposal Endpoint -->
            <div class="border border-gray-300 dark:border-gray-600 rounded-lg p-4">
                <div class="flex items-center gap-2 mb-3">
                    <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300">POST</span>
                    <code class="text-sm bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded">/api/intake-responses/{id}/generate-proposal</code>
                </div>
                <h3 class="text-lg font-medium mb-2">Generate Proposal from Intake Response</h3>
                <p class="mb-3">Creates a comprehensive proposal based on client intake form responses.</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h4 class="font-medium mb-2">Parameters:</h4>
                        <ul class="text-sm space-y-1">
                            <li><code>include_deliverables</code> (boolean) - Include deliverables section</li>
                            <li><code>include_timeline</code> (boolean) - Include timeline section</li>
                            <li><code>include_pricing</code> (boolean) - Include pricing section</li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-medium mb-2">Response:</h4>
                        <ul class="text-sm space-y-1">
                            <li><code>status</code> - "success" or "error"</li>
                            <li><code>message</code> - Human readable message</li>
                            <li><code>data</code> - Generated proposal object</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Generate Project Endpoint -->
            <div class="border border-gray-300 dark:border-gray-600 rounded-lg p-4">
                <div class="flex items-center gap-2 mb-3">
                    <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300">POST</span>
                    <code class="text-sm bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded">/api/proposals/{id}/generate-project</code>
                </div>
                <h3 class="text-lg font-medium mb-2">Generate Project from Proposal</h3>
                <p class="mb-3">Creates a detailed project plan based on an existing proposal.</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h4 class="font-medium mb-2">Parameters:</h4>
                        <ul class="text-sm space-y-1">
                            <li><code>proposal_data</code> (array) - Optional proposal data override</li>
                            <li><code>include_timeline</code> (boolean) - Include project timeline</li>
                            <li><code>include_phases</code> (boolean) - Include project phases</li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-medium mb-2">Response:</h4>
                        <ul class="text-sm space-y-1">
                            <li><code>status</code> - "success" or "error"</li>
                            <li><code>message</code> - Human readable message</li>
                            <li><code>data</code> - Generated project object</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Generate Personal Project Endpoint -->
            <div class="border border-gray-300 dark:border-gray-600 rounded-lg p-4">
                <div class="flex items-center gap-2 mb-3">
                    <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300">POST</span>
                    <code class="text-sm bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded">/api/projects/generate-personal-ai-project</code>
                </div>
                <h3 class="text-lg font-medium mb-2">Generate Personal AI Project</h3>
                <p class="mb-3">Creates a personal project for portfolio or skill development purposes.</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h4 class="font-medium mb-2">Parameters:</h4>
                        <ul class="text-sm space-y-1">
                            <li><code>project_type</code> (string, required) - Type of project</li>
                            <li><code>description</code> (string, required) - Project description</li>
                            <li><code>include_in_portfolio</code> (boolean) - Include in portfolio</li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-medium mb-2">Response:</h4>
                        <ul class="text-sm space-y-1">
                            <li><code>status</code> - "success" or "error"</li>
                            <li><code>message</code> - Human readable message</li>
                            <li><code>data</code> - Generated project object</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Generate Tasks Endpoint -->
            <div class="border border-gray-300 dark:border-gray-600 rounded-lg p-4">
                <div class="flex items-center gap-2 mb-3">
                    <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300">POST</span>
                    <code class="text-sm bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded">/api/projects/{id}/generate-tasks</code>
                </div>
                <h3 class="text-lg font-medium mb-2">Generate Tasks for Project</h3>
                <p class="mb-3">Creates detailed tasks and subtasks for an existing project.</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h4 class="font-medium mb-2">Parameters:</h4>
                        <ul class="text-sm space-y-1">
                            <li><code>project_description</code> (string, required) - Project description</li>
                            <li><code>project_scope</code> (string, required) - Project scope</li>
                            <li><code>timeline</code> (array) - Project timeline</li>
                            <li><code>max_tasks</code> (integer) - Maximum number of tasks (1-20)</li>
                            <li><code>include_subtasks</code> (boolean) - Include subtasks</li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-medium mb-2">Response:</h4>
                        <ul class="text-sm space-y-1">
                            <li><code>status</code> - "success" or "error"</li>
                            <li><code>message</code> - Human readable message</li>
                            <li><code>data</code> - Array of generated tasks</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Generate Personal Tasks Endpoint -->
            <div class="border border-gray-300 dark:border-gray-600 rounded-lg p-4">
                <div class="flex items-center gap-2 mb-3">
                    <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300">POST</span>
                    <code class="text-sm bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded">/api/projects/generate-personal-tasks</code>
                </div>
                <h3 class="text-lg font-medium mb-2">Generate Personal Tasks</h3>
                <p class="mb-3">Creates tasks for personal projects or skill development.</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h4 class="font-medium mb-2">Parameters:</h4>
                        <ul class="text-sm space-y-1">
                            <li><code>project_type</code> (string, required) - Type of project</li>
                            <li><code>project_title</code> (string, required) - Project title</li>
                            <li><code>description</code> (string, required) - Project description</li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-medium mb-2">Response:</h4>
                        <ul class="text-sm space-y-1">
                            <li><code>status</code> - "success" or "error"</li>
                            <li><code>message</code> - Human readable message</li>
                            <li><code>data</code> - Generated tasks data</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
            <h3 class="text-lg font-medium mb-2">Authentication</h3>
            <p class="text-sm mb-2">All endpoints require authentication using Laravel Sanctum. Include the Bearer token in the Authorization header:</p>
            <code class="text-sm bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded">Authorization: Bearer {your-token}</code>
        </div>
    </div>
</x-filament::card>
