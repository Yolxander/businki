<?php

namespace App\Http\Controllers;

use App\Models\ContextEngineeringDocument;
use App\Models\DevProject;
use App\Services\OpenAIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class ContextEngineeringController extends Controller
{
    protected OpenAIService $openAIService;

    public function __construct(OpenAIService $openAIService)
    {
        $this->openAIService = $openAIService;
    }

    /**
     * Display the context engineering documents page
     */
        public function index(Request $request)
    {
        $projects = DevProject::withCount('documents')
            ->orderBy('title')
            ->get();
        $types = ContextEngineeringDocument::getTypes();

        return Inertia::render('ContextEngineering', [
            'auth' => ['user' => Auth::user()],
            'projects' => $projects,
            'types' => $types,
            'filters' => []
        ]);
    }

    /**
     * Display the specified project with its documents
     */
    public function showProject(DevProject $project)
    {
        $project->load(['documents' => function($query) {
            $query->with(['creator'])->orderBy('created_at', 'desc');
        }]);

        $types = ContextEngineeringDocument::getTypes();

        // Calculate stats for this project
        $stats = [
            'total_documents' => $project->documents->count(),
            'generated' => $project->documents->where('is_generated', true)->count(),
            'active' => $project->documents->where('is_active', true)->count(),
        ];

        return Inertia::render('ContextEngineering/ProjectDetails', [
            'auth' => ['user' => Auth::user()],
            'project' => $project,
            'types' => $types,
            'stats' => $stats
        ]);
    }

    /**
     * Show the form for creating a new document
     */
    public function create(Request $request)
    {
        $projectId = $request->query('project_id');
        $type = $request->query('type');

        $projects = DevProject::orderBy('title')->get();
        $types = ContextEngineeringDocument::getTypes();
        $templates = ContextEngineeringDocument::templates()
            ->when($type, fn($q) => $q->where('type', $type))
            ->get();

        return Inertia::render('ContextEngineering/Create', [
            'auth' => ['user' => Auth::user()],
            'projects' => $projects,
            'types' => $types,
            'templates' => $templates,
            'preselected' => [
                'project_id' => $projectId,
                'type' => $type
            ]
        ]);
    }

    /**
     * Store a newly created document
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_id' => 'required|exists:dev_projects,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:' . implode(',', array_keys(ContextEngineeringDocument::getTypes())),
            'content' => 'required|string',
            'is_template' => 'boolean',
            'variables' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], Response::HTTP_BAD_REQUEST);
        }

        $document = ContextEngineeringDocument::create([
            'dev_project_id' => $request->project_id,
            'name' => $request->name,
            'description' => $request->description,
            'type' => $request->type,
            'content' => $request->content,
            'is_template' => $request->boolean('is_template'),
            'variables' => $request->variables,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id()
        ]);

        return response()->json([
            'message' => 'Document created successfully',
            'document' => $document->load(['devProject', 'creator'])
        ]);
    }

    /**
     * Display the specified document
     */
        public function show(ContextEngineeringDocument $document)
    {
        $document->load(['devProject', 'creator', 'updater']);

        return Inertia::render('ContextEngineering/Show', [
            'auth' => ['user' => Auth::user()],
            'document' => $document
        ]);
    }

    /**
     * Show the form for editing the specified document
     */
    public function edit(ContextEngineeringDocument $document)
    {
        $document->load(['devProject', 'creator']);
        $projects = DevProject::orderBy('title')->get();
        $types = ContextEngineeringDocument::getTypes();

        return Inertia::render('ContextEngineering/Edit', [
            'auth' => ['user' => Auth::user()],
            'document' => $document,
            'projects' => $projects,
            'types' => $types
        ]);
    }

    /**
     * Update the specified document
     */
    public function update(Request $request, ContextEngineeringDocument $document)
    {
        $validator = Validator::make($request->all(), [
            'project_id' => 'required|exists:dev_projects,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:' . implode(',', array_keys(ContextEngineeringDocument::getTypes())),
            'content' => 'required|string',
            'is_template' => 'boolean',
            'variables' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], Response::HTTP_BAD_REQUEST);
        }

        $document->update([
            'dev_project_id' => $request->project_id,
            'name' => $request->name,
            'description' => $request->description,
            'type' => $request->type,
            'content' => $request->content,
            'is_template' => $request->boolean('is_template'),
            'variables' => $request->variables,
            'updated_by' => Auth::id()
        ]);

        return response()->json([
            'message' => 'Document updated successfully',
            'document' => $document->load(['devProject', 'creator'])
        ]);
    }

    /**
     * Remove the specified document
     */
    public function destroy(ContextEngineeringDocument $document)
    {
        // Delete associated file if exists
        if ($document->file_path && Storage::disk('local')->exists($document->file_path)) {
            Storage::disk('local')->delete($document->file_path);
        }

        $document->delete();

        return response()->json([
            'message' => 'Document deleted successfully'
        ]);
    }

        /**
     * Create a new development project
     */
    public function createProject(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'required|string|min:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            // Generate project title using AI
            $titlePrompt = "Generate a concise, professional project title (max 60 characters) based on this description: " . $request->description;

            $titleResponse = $this->openAIService->generateChatCompletionWithParams(
                $titlePrompt,
                config('services.openai.model'),
                0.7,
                100
            );

            $projectTitle = trim($titleResponse['content'] ?? 'New Development Project');

            // Remove quotes if present
            $projectTitle = trim($projectTitle, '"\'');

            // Limit to 60 characters
            if (strlen($projectTitle) > 60) {
                $projectTitle = substr($projectTitle, 0, 57) . '...';
            }

            $project = DevProject::create([
                'title' => $projectTitle,
                'description' => $request->description,
                'is_generated' => true,
                'generation_metadata' => [
                    'original_description' => $request->description,
                    'model' => config('services.openai.model'),
                    'tokens_used' => $titleResponse['usage']['total_tokens'] ?? null,
                    'cost' => $titleResponse['cost'] ?? null
                ],
                'created_by' => Auth::id(),
                'updated_by' => Auth::id()
            ]);

            return response()->json([
                'message' => 'Project created successfully',
                'project' => $project->load(['creator'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create project: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Generate document using AI
     */
    public function generate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_id' => 'required|exists:dev_projects,id',
            'type' => 'required|in:' . implode(',', array_keys(ContextEngineeringDocument::getTypes())),
            'prompt' => 'required|string',
            'options' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $project = DevProject::findOrFail($request->project_id);

            // Build context for AI generation
            $context = $this->buildGenerationContext($project, $request->type, $request->prompt);

            // Generate content using AI
            $response = $this->openAIService->generateChatCompletionWithParams(
                $context,
                config('services.openai.model'),
                0.7,
                4000
            );

            if (!isset($response['content'])) {
                throw new \Exception('Failed to generate content');
            }

            $generatedContent = $response['content'];
            $documentName = $this->generateDocumentName($request->type, $project);

            $document = ContextEngineeringDocument::create([
                'dev_project_id' => $request->project_id,
                'name' => $documentName,
                'description' => 'AI-generated ' . ContextEngineeringDocument::getTypes()[$request->type],
                'type' => $request->type,
                'content' => $generatedContent,
                'is_generated' => true,
                'generation_metadata' => [
                    'prompt' => $request->prompt,
                    'options' => $request->options,
                    'model' => config('services.openai.model'),
                    'tokens_used' => $response['usage']['total_tokens'] ?? null,
                    'cost' => $response['cost'] ?? null
                ],
                'created_by' => Auth::id(),
                'updated_by' => Auth::id()
            ]);

            return response()->json([
                'message' => 'Document generated successfully',
                'document' => $document->load(['devProject', 'creator'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to generate document: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Download document as file
     */
    public function download(ContextEngineeringDocument $document)
    {
        $fileName = $document->generateFileName();
        $content = $document->content;

        // Add metadata header for markdown files
        if ($document->isMarkdown()) {
            $content = $this->addMarkdownMetadata($document) . "\n\n" . $content;
        }

        return response($content)
            ->header('Content-Type', $document->mime_type ?? 'text/plain')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    }

        /**
     * Upload file and create document
     */
    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_id' => 'required|exists:dev_projects,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:' . implode(',', array_keys(ContextEngineeringDocument::getTypes())),
            'file' => 'required|file|max:10240' // 10MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], Response::HTTP_BAD_REQUEST);
        }

        $file = $request->file('file');
        $content = file_get_contents($file->getRealPath());

        // Store file
        $filePath = 'context-engineering/' . time() . '_' . $file->getClientOriginalName();
        Storage::disk('local')->put($filePath, $content);

        $document = ContextEngineeringDocument::create([
            'dev_project_id' => $request->project_id,
            'name' => $request->name,
            'description' => $request->description,
            'type' => $request->type,
            'content' => $content,
            'file_path' => $filePath,
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'created_by' => Auth::id(),
            'updated_by' => Auth::id()
        ]);

        return response()->json([
            'message' => 'File uploaded successfully',
            'document' => $document->load(['devProject', 'creator'])
        ]);
    }

    /**
     * Create new version of document
     */
    public function createVersion(ContextEngineeringDocument $document)
    {
        $newVersion = $document->createNewVersion();
        $newVersion->updated_by = Auth::id();
        $newVersion->save();

        return response()->json([
            'message' => 'New version created successfully',
            'document' => $newVersion->load(['project', 'creator'])
        ]);
    }

    /**
     * Activate document version
     */
    public function activate(ContextEngineeringDocument $document)
    {
        $document->activate();

        return response()->json([
            'message' => 'Document version activated successfully',
            'document' => $document->load(['project', 'creator'])
        ]);
    }

    /**
     * Get document templates
     */
    public function templates(Request $request)
    {
        $type = $request->query('type');

        $templates = ContextEngineeringDocument::templates()
            ->with(['creator'])
            ->when($type, fn($q) => $q->where('type', $type))
            ->orderBy('name')
            ->get();

        return response()->json([
            'templates' => $templates
        ]);
    }

        /**
     * Build context for AI generation
     */
    private function buildGenerationContext(DevProject $project, string $type, string $prompt): string
    {
        $context = "Project: {$project->title}\n";
        $context .= "Description: {$project->description}\n";
        $context .= "Type: " . ContextEngineeringDocument::getTypes()[$type] . "\n\n";

        $context .= "\nUser Request: {$prompt}\n\n";

        // Add type-specific instructions
        $context .= $this->getTypeSpecificInstructions($type);

        return $context;
    }

    /**
     * Get type-specific generation instructions
     */
    private function getTypeSpecificInstructions(string $type): string
    {
        return match ($type) {
            ContextEngineeringDocument::TYPE_IMPLEMENTATION =>
                "Please create a comprehensive implementation plan with:\n" .
                "- Feature analysis and categorization\n" .
                "- Recommended tech stack with documentation links\n" .
                "- Implementation stages with checkboxes\n" .
                "- Resource links and timeline estimates\n" .
                "Format as markdown with proper structure.",

            ContextEngineeringDocument::TYPE_WORKFLOW =>
                "Please create a development workflow document with:\n" .
                "- Clear workflow process steps\n" .
                "- Task execution protocols\n" .
                "- File reference priorities\n" .
                "- Critical rules and guidelines\n" .
                "Format as markdown with proper structure.",

            ContextEngineeringDocument::TYPE_PROJECT_STRUCTURE =>
                "Please create a project structure document with:\n" .
                "- Detailed folder hierarchy\n" .
                "- File organization patterns\n" .
                "- Module/component structure\n" .
                "- Configuration and build structure\n" .
                "Format as markdown with proper structure.",

            ContextEngineeringDocument::TYPE_UI_UX =>
                "Please create a UI/UX documentation with:\n" .
                "- Design system specifications\n" .
                "- Component guidelines\n" .
                "- User experience flows\n" .
                "- Responsive design requirements\n" .
                "Format as markdown with proper structure.",

            default => "Please create a comprehensive document based on the user's request. Format as markdown with proper structure."
        };
    }

    /**
     * Generate document name based on type and project
     */
    private function generateDocumentName(string $type, DevProject $project): string
    {
        $typeLabel = str_replace(' ', '', ContextEngineeringDocument::getTypes()[$type]);
        return "{$project->title} - {$typeLabel}";
    }

    /**
     * Add metadata to markdown content
     */
    private function addMarkdownMetadata(ContextEngineeringDocument $document): string
    {
        $metadata = [
            'title' => $document->name,
            'description' => $document->description,
            'type' => $document->type,
            'version' => $document->version,
            'created' => $document->created_at->toISOString(),
            'updated' => $document->updated_at->toISOString(),
            'project' => $document->devProject->title,
            'creator' => $document->creator->name,
            'is_generated' => $document->is_generated,
            'is_template' => $document->is_template
        ];

        $yaml = "---\n";
        foreach ($metadata as $key => $value) {
            $yaml .= "{$key}: " . (is_bool($value) ? ($value ? 'true' : 'false') : $value) . "\n";
        }
        $yaml .= "---\n";

        return $yaml;
    }
}
