<x-filament::card class="dark:bg-gray-900 dark:text-gray-100">
    <div class="prose max-w-none dark:prose-invert">
        <h2 class="text-xl font-semibold mb-4">Response Examples</h2>

        <div class="space-y-6">
            <!-- Generate Proposal Response Example -->
            <div class="border border-gray-300 dark:border-gray-600 rounded-lg p-4">
                <h3 class="text-lg font-medium mb-3">Generate Proposal Response</h3>

                <div class="space-y-4">
                    <div>
                        <h4 class="font-medium mb-2">Success Response (201):</h4>
                        <pre class="bg-gray-100 dark:bg-gray-800 p-3 rounded text-sm overflow-x-auto"><code>{
  "status": "success",
  "message": "Proposal generated successfully",
  "data": {
    "id": 1,
    "intake_response_id": 5,
    "user_id": 1,
    "title": "E-commerce Website Development Proposal",
    "scope": "Complete e-commerce platform with user authentication, product catalog, shopping cart, and payment processing",
    "deliverables": [
      "Responsive website design",
      "User authentication system",
      "Product management system",
      "Shopping cart functionality",
      "Payment gateway integration",
      "Admin dashboard",
      "SEO optimization"
    ],
    "timeline": "8-10 weeks",
    "price": 15000,
    "status": "draft",
    "created_at": "2024-01-15T10:30:00.000000Z",
    "updated_at": "2024-01-15T10:30:00.000000Z",
    "intake_response": {
      "id": 5,
      "client_name": "John Doe",
      "company_name": "TechCorp Inc.",
      "project_description": "Need an e-commerce website for our online store"
    }
  }
}</code></pre>
                    </div>

                    <div>
                        <h4 class="font-medium mb-2">Error Response (409):</h4>
                        <pre class="bg-gray-100 dark:bg-gray-800 p-3 rounded text-sm overflow-x-auto"><code>{
  "status": "error",
  "message": "Proposal already exists for this intake response"
}</code></pre>
                    </div>
                </div>
            </div>

            <!-- Generate Project Response Example -->
            <div class="border border-gray-300 dark:border-gray-600 rounded-lg p-4">
                <h3 class="text-lg font-medium mb-3">Generate Project Response</h3>

                <div class="space-y-4">
                    <div>
                        <h4 class="font-medium mb-2">Success Response (201):</h4>
                        <pre class="bg-gray-100 dark:bg-gray-800 p-3 rounded text-sm overflow-x-auto"><code>{
  "status": "success",
  "message": "Project generated successfully",
  "data": {
    "id": 1,
    "proposal_id": 1,
    "title": "E-commerce Platform Development",
    "status": "not_started",
    "current_phase": "Planning",
    "kickoff_date": "2024-02-01",
    "expected_delivery": "2024-04-15",
    "notes": "Project includes frontend development, backend API, database design, and third-party integrations",
    "created_at": "2024-01-15T10:30:00.000000Z",
    "updated_at": "2024-01-15T10:30:00.000000Z",
    "proposal": {
      "id": 1,
      "title": "E-commerce Website Development Proposal",
      "scope": "Complete e-commerce platform...",
      "price": 15000
    }
  }
}</code></pre>
                    </div>
                </div>
            </div>

            <!-- Generate Personal Project Response Example -->
            <div class="border border-gray-300 dark:border-gray-600 rounded-lg p-4">
                <h3 class="text-lg font-medium mb-3">Generate Personal Project Response</h3>

                <div class="space-y-4">
                    <div>
                        <h4 class="font-medium mb-2">Success Response (201):</h4>
                        <pre class="bg-gray-100 dark:bg-gray-800 p-3 rounded text-sm overflow-x-auto"><code>{
  "status": "success",
  "message": "Personal project generated successfully",
  "data": {
    "id": 2,
    "proposal_id": null,
    "title": "React Hooks Learning Platform",
    "status": "not_started",
    "current_phase": "Planning",
    "kickoff_date": "2024-01-20",
    "expected_delivery": "2024-03-20",
    "notes": "Personal learning project to master React Hooks and create a comprehensive learning platform",
    "created_at": "2024-01-15T10:30:00.000000Z",
    "updated_at": "2024-01-15T10:30:00.000000Z"
  }
}</code></pre>
                    </div>
                </div>
            </div>

            <!-- Generate Tasks Response Example -->
            <div class="border border-gray-300 dark:border-gray-600 rounded-lg p-4">
                <h3 class="text-lg font-medium mb-3">Generate Tasks Response</h3>

                <div class="space-y-4">
                    <div>
                        <h4 class="font-medium mb-2">Success Response (201):</h4>
                        <pre class="bg-gray-100 dark:bg-gray-800 p-3 rounded text-sm overflow-x-auto"><code>{
  "status": "success",
  "message": "Tasks generated successfully",
  "data": [
    {
      "id": 1,
      "project_id": 1,
      "title": "Project Setup and Planning",
      "description": "Initialize project repository, set up development environment, and create project documentation",
      "status": "todo",
      "priority": "high",
      "estimated_hours": 8,
      "tags": ["setup", "planning"],
      "created_at": "2024-01-15T10:30:00.000000Z",
      "updated_at": "2024-01-15T10:30:00.000000Z",
      "subtasks": [
        {
          "id": 1,
          "task_id": 1,
          "description": "Set up Git repository and branching strategy",
          "status": "todo",
          "created_at": "2024-01-15T10:30:00.000000Z"
        },
        {
          "id": 2,
          "task_id": 1,
          "description": "Configure development environment",
          "status": "todo",
          "created_at": "2024-01-15T10:30:00.000000Z"
        }
      ]
    },
    {
      "id": 2,
      "project_id": 1,
      "title": "Database Design",
      "description": "Design and implement database schema for e-commerce platform",
      "status": "todo",
      "priority": "high",
      "estimated_hours": 12,
      "tags": ["database", "backend"],
      "created_at": "2024-01-15T10:30:00.000000Z",
      "updated_at": "2024-01-15T10:30:00.000000Z",
      "subtasks": [
        {
          "id": 3,
          "task_id": 2,
          "description": "Design user authentication tables",
          "status": "todo",
          "created_at": "2024-01-15T10:30:00.000000Z"
        },
        {
          "id": 4,
          "task_id": 2,
          "description": "Design product catalog tables",
          "status": "todo",
          "created_at": "2024-01-15T10:30:00.000000Z"
        }
      ]
    }
  ]
}</code></pre>
                    </div>
                </div>
            </div>

            <!-- Generate Personal Tasks Response Example -->
            <div class="border border-gray-300 dark:border-gray-600 rounded-lg p-4">
                <h3 class="text-lg font-medium mb-3">Generate Personal Tasks Response</h3>

                <div class="space-y-4">
                    <div>
                        <h4 class="font-medium mb-2">Success Response (200):</h4>
                        <pre class="bg-gray-100 dark:bg-gray-800 p-3 rounded text-sm overflow-x-auto"><code>{
  "status": "success",
  "message": "Personal tasks generated successfully",
  "data": {
    "project_type": "Learning Project",
    "project_title": "Master React Hooks",
    "tasks": [
      {
        "title": "Understanding useState Hook",
        "description": "Learn the basics of useState hook for managing component state",
        "estimated_hours": 4,
        "priority": "high",
        "subtasks": [
          "Read React documentation on useState",
          "Practice with simple counter examples",
          "Build a form component using useState"
        ]
      },
      {
        "title": "Mastering useEffect Hook",
        "description": "Learn useEffect for handling side effects in functional components",
        "estimated_hours": 6,
        "priority": "high",
        "subtasks": [
          "Understand dependency arrays",
          "Practice API calls with useEffect",
          "Learn cleanup functions"
        ]
      }
    ]
  }
}</code></pre>
                    </div>
                </div>
            </div>

            <!-- Common Error Responses -->
            <div class="border border-gray-300 dark:border-gray-600 rounded-lg p-4">
                <h3 class="text-lg font-medium mb-3">Common Error Responses</h3>

                <div class="space-y-4">
                    <div>
                        <h4 class="font-medium mb-2">Validation Error (422):</h4>
                        <pre class="bg-gray-100 dark:bg-gray-800 p-3 rounded text-sm overflow-x-auto"><code>{
  "status": "error",
  "message": "Validation failed",
  "errors": {
    "project_type": [
      "The project type field is required."
    ],
    "description": [
      "The description field is required."
    ]
  }
}</code></pre>
                    </div>

                    <div>
                        <h4 class="font-medium mb-2">Authentication Error (401):</h4>
                        <pre class="bg-gray-100 dark:bg-gray-800 p-3 rounded text-sm overflow-x-auto"><code>{
  "message": "Unauthenticated."
}</code></pre>
                    </div>

                    <div>
                        <h4 class="font-medium mb-2">Server Error (500):</h4>
                        <pre class="bg-gray-100 dark:bg-gray-800 p-3 rounded text-sm overflow-x-auto"><code>{
  "status": "error",
  "message": "Failed to generate proposal: OpenAI API error occurred"
}</code></pre>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
            <h3 class="text-lg font-medium mb-2">Response Structure</h3>
            <ul class="text-sm space-y-1">
                <li>• <code>status</code> - Always "success" or "error"</li>
                <li>• <code>message</code> - Human-readable description of the result</li>
                <li>• <code>data</code> - Contains the generated content or resource</li>
                <li>• <code>errors</code> - Present only on validation errors (422)</li>
                <li>• All timestamps are in ISO 8601 format</li>
                <li>• IDs are integers representing database primary keys</li>
            </ul>
        </div>
    </div>
</x-filament::card>
