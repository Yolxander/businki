<x-filament::card class="dark:bg-gray-900 dark:text-gray-100">
    <div class="prose max-w-none dark:prose-invert">
        <h2 class="text-xl font-semibold mb-4">Request Examples</h2>

        <div class="space-y-6">
            <!-- Generate Proposal Request Example -->
            <div class="border border-gray-300 dark:border-gray-600 rounded-lg p-4">
                <h3 class="text-lg font-medium mb-3">Generate Proposal from Intake Response</h3>

                <div class="space-y-4">
                    <div>
                        <h4 class="font-medium mb-2">cURL Example:</h4>
                        <pre class="bg-gray-100 dark:bg-gray-800 p-3 rounded text-sm overflow-x-auto"><code>curl -X POST "{{ url('/api/intake-responses/1/generate-proposal') }}" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "include_deliverables": true,
    "include_timeline": true,
    "include_pricing": true
  }'</code></pre>
                    </div>

                    <div>
                        <h4 class="font-medium mb-2">JavaScript (Fetch):</h4>
                        <pre class="bg-gray-100 dark:bg-gray-800 p-3 rounded text-sm overflow-x-auto"><code>const response = await fetch('/api/intake-responses/1/generate-proposal', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer YOUR_TOKEN',
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    include_deliverables: true,
    include_timeline: true,
    include_pricing: true
  })
});

const data = await response.json();</code></pre>
                    </div>

                    <div>
                        <h4 class="font-medium mb-2">PHP Example:</h4>
                        <pre class="bg-gray-100 dark:bg-gray-800 p-3 rounded text-sm overflow-x-auto"><code>$response = Http::withToken('YOUR_TOKEN')
    ->post('/api/intake-responses/1/generate-proposal', [
        'include_deliverables' => true,
        'include_timeline' => true,
        'include_pricing' => true
    ]);

$data = $response->json();</code></pre>
                    </div>
                </div>
            </div>

            <!-- Generate Project Request Example -->
            <div class="border border-gray-300 dark:border-gray-600 rounded-lg p-4">
                <h3 class="text-lg font-medium mb-3">Generate Project from Proposal</h3>

                <div class="space-y-4">
                    <div>
                        <h4 class="font-medium mb-2">cURL Example:</h4>
                        <pre class="bg-gray-100 dark:bg-gray-800 p-3 rounded text-sm overflow-x-auto"><code>curl -X POST "{{ url('/api/proposals/1/generate-project') }}" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "include_timeline": true,
    "include_phases": true
  }'</code></pre>
                    </div>

                    <div>
                        <h4 class="font-medium mb-2">JavaScript (Fetch):</h4>
                        <pre class="bg-gray-100 dark:bg-gray-800 p-3 rounded text-sm overflow-x-auto"><code>const response = await fetch('/api/proposals/1/generate-project', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer YOUR_TOKEN',
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    include_timeline: true,
    include_phases: true
  })
});

const data = await response.json();</code></pre>
                    </div>
                </div>
            </div>

            <!-- Generate Personal Project Request Example -->
            <div class="border border-gray-300 dark:border-gray-600 rounded-lg p-4">
                <h3 class="text-lg font-medium mb-3">Generate Personal AI Project</h3>

                <div class="space-y-4">
                    <div>
                        <h4 class="font-medium mb-2">cURL Example:</h4>
                        <pre class="bg-gray-100 dark:bg-gray-800 p-3 rounded text-sm overflow-x-auto"><code>curl -X POST "{{ url('/api/projects/generate-personal-ai-project') }}" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "project_type": "Web Application",
    "description": "A modern e-commerce platform with React frontend and Laravel backend",
    "include_in_portfolio": true
  }'</code></pre>
                    </div>

                    <div>
                        <h4 class="font-medium mb-2">JavaScript (Fetch):</h4>
                        <pre class="bg-gray-100 dark:bg-gray-800 p-3 rounded text-sm overflow-x-auto"><code>const response = await fetch('/api/projects/generate-personal-ai-project', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer YOUR_TOKEN',
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    project_type: 'Web Application',
    description: 'A modern e-commerce platform with React frontend and Laravel backend',
    include_in_portfolio: true
  })
});

const data = await response.json();</code></pre>
                    </div>
                </div>
            </div>

            <!-- Generate Tasks Request Example -->
            <div class="border border-gray-300 dark:border-gray-600 rounded-lg p-4">
                <h3 class="text-lg font-medium mb-3">Generate Tasks for Project</h3>

                <div class="space-y-4">
                    <div>
                        <h4 class="font-medium mb-2">cURL Example:</h4>
                        <pre class="bg-gray-100 dark:bg-gray-800 p-3 rounded text-sm overflow-x-auto"><code>curl -X POST "{{ url('/api/projects/1/generate-tasks') }}" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "project_description": "A comprehensive e-commerce platform",
    "project_scope": "Full-stack development including frontend, backend, and database",
    "timeline": {
      "start_date": "2024-01-01",
      "end_date": "2024-03-31"
    },
    "max_tasks": 15,
    "include_subtasks": true
  }'</code></pre>
                    </div>

                    <div>
                        <h4 class="font-medium mb-2">JavaScript (Fetch):</h4>
                        <pre class="bg-gray-100 dark:bg-gray-800 p-3 rounded text-sm overflow-x-auto"><code>const response = await fetch('/api/projects/1/generate-tasks', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer YOUR_TOKEN',
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    project_description: 'A comprehensive e-commerce platform',
    project_scope: 'Full-stack development including frontend, backend, and database',
    timeline: {
      start_date: '2024-01-01',
      end_date: '2024-03-31'
    },
    max_tasks: 15,
    include_subtasks: true
  })
});

const data = await response.json();</code></pre>
                    </div>
                </div>
            </div>

            <!-- Generate Personal Tasks Request Example -->
            <div class="border border-gray-300 dark:border-gray-600 rounded-lg p-4">
                <h3 class="text-lg font-medium mb-3">Generate Personal Tasks</h3>

                <div class="space-y-4">
                    <div>
                        <h4 class="font-medium mb-2">cURL Example:</h4>
                        <pre class="bg-gray-100 dark:bg-gray-800 p-3 rounded text-sm overflow-x-auto"><code>curl -X POST "{{ url('/api/projects/generate-personal-tasks') }}" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "project_type": "Learning Project",
    "project_title": "Master React Hooks",
    "description": "Complete understanding of React Hooks including useState, useEffect, useContext, and custom hooks"
  }'</code></pre>
                    </div>

                    <div>
                        <h4 class="font-medium mb-2">JavaScript (Fetch):</h4>
                        <pre class="bg-gray-100 dark:bg-gray-800 p-3 rounded text-sm overflow-x-auto"><code>const response = await fetch('/api/projects/generate-personal-tasks', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer YOUR_TOKEN',
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    project_type: 'Learning Project',
    project_title: 'Master React Hooks',
    description: 'Complete understanding of React Hooks including useState, useEffect, useContext, and custom hooks'
  })
});

const data = await response.json();</code></pre>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
            <h3 class="text-lg font-medium mb-2">Important Notes</h3>
            <ul class="text-sm space-y-1">
                <li>• Replace <code>YOUR_TOKEN</code> with your actual authentication token</li>
                <li>• Replace ID placeholders (like <code>1</code>) with actual resource IDs</li>
                <li>• All requests must include the <code>Authorization</code> header</li>
                <li>• Content-Type should be <code>application/json</code> for POST requests</li>
                <li>• Boolean parameters default to <code>true</code> if not specified</li>
            </ul>
        </div>
    </div>
</x-filament::card>
