<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContextEngineeringDocument;
use App\Models\DevProject;
use App\Services\OpenAIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class ContextEngineeringController extends Controller
{
    protected OpenAIService $openAIService;

    public function __construct(OpenAIService $openAIService)
    {
        $this->openAIService = $openAIService;
    }

    /**
     * Get all documents with filters
     */
        public function index(Request $request)
    {
        $projectId = $request->query('project_id');
        $type = $request->query('type');
        $isTemplate = $request->query('is_template');
        $isGenerated = $request->query('is_generated');

        $query = ContextEngineeringDocument::with(['devProject', 'creator'])
            ->when($projectId, fn($q) => $q->where('dev_project_id', $projectId))
            ->when($type, fn($q) => $q->where('type', $type))
            ->when($isTemplate !== null, fn($q) => $q->where('is_template', $isTemplate))
            ->when($isGenerated !== null, fn($q) => $q->where('is_generated', $isGenerated))
            ->orderBy('created_at', 'desc');

        $documents = $query->paginate($request->query('per_page', 20));

        return response()->json([
            'documents' => $documents,
            'filters' => [
                'project_id' => $projectId,
                'type' => $type,
                'is_template' => $isTemplate,
                'is_generated' => $isGenerated
            ]
        ]);
    }

    /**
     * Get document details
     */
        public function show(ContextEngineeringDocument $document)
    {
        $document->load(['devProject', 'creator', 'updater']);

        return response()->json([
            'document' => $document
        ]);
    }

    /**
     * Store new document
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
        ], Response::HTTP_CREATED);
    }

    /**
     * Update document
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
     * Delete document
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
            'document' => $newVersion->load(['devProject', 'creator'])
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
            'document' => $document->load(['devProject', 'creator'])
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
     * Get projects for dropdown
     */
    public function projects()
    {
        $projects = DevProject::orderBy('title')->get(['id', 'title']);

        return response()->json([
            'projects' => $projects
        ]);
    }

    /**
     * Get document types
     */
    public function types()
    {
        return response()->json([
            'types' => ContextEngineeringDocument::getTypes()
        ]);
    }

    /**
     * Get document statistics
     */
    public function stats(Request $request)
    {
        $totalProjects = DevProject::count();
        $totalDocuments = ContextEngineeringDocument::count();
        $generatedDocuments = ContextEngineeringDocument::where('is_generated', true)->count();
        $activeDocuments = ContextEngineeringDocument::where('is_active', true)->count();

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_projects' => $totalProjects,
                'total_documents' => $totalDocuments,
                'generated' => $generatedDocuments,
                'active' => $activeDocuments
            ]
        ]);
    }

    /**
     * Delete a development project
     */
    public function deleteProject(DevProject $project)
    {
        // Delete all associated documents first
        $project->documents()->delete();

        // Delete the project
        $project->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Project and all associated documents deleted successfully'
        ]);
    }

    /**
     * Regenerate a document using AI
     */
    public function regenerateDocument(ContextEngineeringDocument $document)
    {
        try {
            // Get the original prompt or generate one based on the document
            $originalPrompt = $document->generation_metadata['prompt'] ?? 'Regenerate this document with improvements';

            // Generate new content
            $context = $this->buildGenerationContext($document->devProject, $document->type, $originalPrompt);

            $response = $this->openAIService->generateChatCompletionWithParams(
                $context,
                config('services.openai.model'),
                0.7,
                2000
            );

            $newContent = $response['content'] ?? '';

            // Create a new version
            $newDocument = $document->replicate();
            $newDocument->content = $newContent;
            $newDocument->version = $document->version + 1;
            $newDocument->is_generated = true;
            $newDocument->generation_metadata = [
                'prompt' => $originalPrompt,
                'regenerated_from' => $document->id,
                'regenerated_at' => now()->toISOString()
            ];
            $newDocument->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Document regenerated successfully',
                'data' => $newDocument->load(['devProject', 'creator'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to regenerate document: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
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
}
