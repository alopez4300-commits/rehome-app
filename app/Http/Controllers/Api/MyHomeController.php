<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\MyHome\MyHomeService;
use App\Services\MyHome\MyHomeQueryService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MyHomeController extends Controller
{
    private MyHomeService $myHomeService;
    private MyHomeQueryService $queryService;

    public function __construct(MyHomeService $myHomeService, MyHomeQueryService $queryService)
    {
        $this->myHomeService = $myHomeService;
        $this->queryService = $queryService;
    }

    /**
     * Get activity feed for a project
     */
    public function getFeed(Request $request, Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $limit = $request->get('limit', 50);
        $offset = $request->get('offset', 0);

        $feed = $this->queryService->getActivityFeed($project, $limit, $offset);

        return response()->json([
            'success' => true,
            'data' => $feed
        ]);
    }

    /**
     * Create a new note
     */
    public function createNote(Request $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $validator = Validator::make($request->all(), [
            'text' => 'required|string|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $entry = $this->myHomeService->createNote(
            $project,
            Auth::user(),
            $request->input('text')
        );

        return response()->json([
            'success' => true,
            'data' => $entry
        ], 201);
    }

    /**
     * Create a new task
     */
    public function createTask(Request $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'due' => 'nullable|date',
            'status' => 'nullable|string|in:pending,in_progress,completed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $entry = $this->myHomeService->createTask(
            $project,
            Auth::user(),
            $request->input('title'),
            $request->input('description'),
            $request->input('due'),
            $request->input('status', 'pending')
        );

        return response()->json([
            'success' => true,
            'data' => $entry
        ], 201);
    }

    /**
     * Create a time log entry
     */
    public function createTimeLog(Request $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $validator = Validator::make($request->all(), [
            'hours' => 'required|numeric|min:0.1|max:24',
            'task' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $entry = $this->myHomeService->createTimeLog(
            $project,
            Auth::user(),
            $request->input('hours'),
            $request->input('task'),
            $request->input('description')
        );

        return response()->json([
            'success' => true,
            'data' => $entry
        ], 201);
    }

    /**
     * Search MyHome entries
     */
    public function search(Request $request, Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:2|max:255',
            'kind' => 'nullable|string',
            'author' => 'nullable|integer',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $filters = $request->only(['query', 'kind', 'author', 'date_from', 'date_to']);
        $results = $this->queryService->advancedSearch($project, $filters);

        return response()->json([
            'success' => true,
            'data' => $results
        ]);
    }

    /**
     * Get project statistics
     */
    public function getStats(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $stats = $this->myHomeService->getStats($project);

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get recent activity summary
     */
    public function getRecentActivity(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $days = request()->get('days', 7);
        $summary = $this->queryService->getRecentActivitySummary($project, $days);

        return response()->json([
            'success' => true,
            'data' => $summary
        ]);
    }

    /**
     * Get project health metrics
     */
    public function getProjectHealth(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $health = $this->queryService->getProjectHealth($project);

        return response()->json([
            'success' => true,
            'data' => $health
        ]);
    }

    /**
     * Get tasks for a project
     */
    public function getTasks(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $tasks = $this->myHomeService->getTasks($project);

        return response()->json([
            'success' => true,
            'data' => $tasks
        ]);
    }

    /**
     * Get time logs for a project
     */
    public function getTimeLogs(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $timeLogs = $this->myHomeService->getTimeLogs($project);

        return response()->json([
            'success' => true,
            'data' => $timeLogs
        ]);
    }

    /**
     * Get files for a project
     */
    public function getFiles(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $files = $this->myHomeService->getFiles($project);

        return response()->json([
            'success' => true,
            'data' => $files
        ]);
    }

    /**
     * Get AI interactions for a project
     */
    public function getAIInteractions(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $interactions = $this->myHomeService->getAIInteractions($project);

        return response()->json([
            'success' => true,
            'data' => $interactions
        ]);
    }

    /**
     * Get timeline for a project
     */
    public function getTimeline(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $limit = request()->get('limit', 100);
        $timeline = $this->queryService->getTimeline($project, $limit);

        return response()->json([
            'success' => true,
            'data' => $timeline
        ]);
    }

    /**
     * Get entries by kind
     */
    public function getByKind(Request $request, Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $validator = Validator::make($request->all(), [
            'kind' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $entries = $this->myHomeService->getByKind($project, $request->input('kind'));

        return response()->json([
            'success' => true,
            'data' => $entries
        ]);
    }

    /**
     * Get entries by author
     */
    public function getByAuthor(Request $request, Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $validator = Validator::make($request->all(), [
            'author_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $entries = $this->queryService->getByAuthor($project, $request->input('author_id'));

        return response()->json([
            'success' => true,
            'data' => $entries
        ]);
    }
}