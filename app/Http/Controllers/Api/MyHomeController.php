<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\MyHome\MyHomeService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MyHomeController extends Controller
{
    public function __construct(
        private MyHomeService $myHomeService
    ) {}

    /**
     * List MyHome entries for a project
     * GET /api/projects/{project}/myhome
     */
    public function index(Request $request, Project $project): JsonResponse
    {
        $this->authorize('accessMyHome', $project);

        $limit = $request->integer('limit', 50);
        $kind = $request->string('kind');

        if ($kind->isNotEmpty()) {
            $entries = $this->myHomeService->getByKind($project, $kind->toString());
        } else {
            $entries = $this->myHomeService->read($project, $limit);
        }

        return response()->json([
            'success' => true,
            'data' => $entries,
            'meta' => [
                'project_id' => $project->id,
                'project_name' => $project->name,
                'workspace_id' => $project->workspace_id,
                'total_entries' => $entries->count(),
            ]
        ]);
    }

    /**
     * Add a new MyHome entry
     * POST /api/projects/{project}/myhome
     */
    public function store(Request $request, Project $project): JsonResponse
    {
        $this->authorize('addToMyHome', $project);

        $validated = $request->validate([
            'kind' => 'required|string|max:50',
            'content' => 'nullable|string',
            'metadata' => 'nullable|array',
        ]);

        $entry = $this->myHomeService->append(
            $project,
            $request->user(),
            $validated
        );

        return response()->json([
            'success' => true,
            'data' => $entry,
            'message' => 'Entry added to MyHome successfully'
        ], 201);
    }

    /**
     * Search MyHome entries
     * GET /api/projects/{project}/myhome/search
     */
    public function search(Request $request, Project $project): JsonResponse
    {
        $this->authorize('accessMyHome', $project);

        $request->validate([
            'q' => 'required|string|min:2',
        ]);

        $query = $request->string('q');
        $entries = $this->myHomeService->search($project, $query->toString());

        return response()->json([
            'success' => true,
            'data' => $entries,
            'meta' => [
                'query' => $query,
                'results_count' => $entries->count(),
                'project_id' => $project->id,
            ]
        ]);
    }

    /**
     * Add a comment entry (convenience method)
     * POST /api/projects/{project}/myhome/comment
     */
    public function addComment(Request $request, Project $project): JsonResponse
    {
        $this->authorize('addToMyHome', $project);

        $request->validate([
            'content' => 'required|string|max:2000',
        ]);

        $entry = $this->myHomeService->addComment(
            $project,
            $request->user(),
            $request->string('content')->toString()
        );

        return response()->json([
            'success' => true,
            'data' => $entry,
            'message' => 'Comment added successfully'
        ], 201);
    }

    /**
     * Add a status change entry (convenience method)
     * POST /api/projects/{project}/myhome/status
     */
    public function addStatusChange(Request $request, Project $project): JsonResponse
    {
        $this->authorize('addToMyHome', $project);

        $request->validate([
            'old_status' => 'required|string',
            'new_status' => 'required|string',
        ]);

        $entry = $this->myHomeService->addStatusChange(
            $project,
            $request->user(),
            $request->string('old_status')->toString(),
            $request->string('new_status')->toString()
        );

        return response()->json([
            'success' => true,
            'data' => $entry,
            'message' => 'Status change recorded successfully'
        ], 201);
    }

    /**
     * Get recent activity across accessible projects
     * GET /api/myhome/activity
     */
    public function activity(Request $request): JsonResponse
    {
        $limit = $request->integer('limit', 25);
        
        $entries = $this->myHomeService->getRecentActivity(
            $request->user(),
            $limit
        );

        return response()->json([
            'success' => true,
            'data' => $entries,
            'meta' => [
                'total_entries' => $entries->count(),
                'user_id' => $request->user()->id,
            ]
        ]);
    }
}
