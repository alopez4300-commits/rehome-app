<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\Agent\AgentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AgentController extends Controller
{
    private AgentService $agentService;

    public function __construct(AgentService $agentService)
    {
        $this->agentService = $agentService;
    }

    /**
     * Process AI request
     */
    public function processRequest(Request $request, Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:1|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $response = $this->agentService->processRequest(
            $project,
            Auth::user(),
            $request->input('query')
        );

        if (!$response['success']) {
            return response()->json([
                'success' => false,
                'error' => $response['error']
            ], 500);
        }

        return response()->json([
            'success' => true,
            'data' => $response
        ]);
    }

    /**
     * Get user AI usage statistics
     */
    public function getUserStats(): JsonResponse
    {
        $stats = $this->agentService->getUserStats(Auth::user());

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get project AI usage statistics
     */
    public function getProjectStats(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $stats = $this->agentService->getProjectStats($project);

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}