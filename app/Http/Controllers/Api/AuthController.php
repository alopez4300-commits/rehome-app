<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login user and return user data with role information
     * POST /api/login
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Delete existing tokens
        $user->tokens()->delete();

        // Create new token
        $token = $user->createToken('spa-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'has_admin_role' => $user->has_admin_role,
                ],
                'token' => $token,
                'expires_at' => now()->addDays(30)->toISOString(),
            ],
            'message' => 'Login successful'
        ]);
    }

    /**
     * Get current authenticated user
     * GET /api/me
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'has_admin_role' => $user->has_admin_role,
                ],
            ]
        ]);
    }

    /**
     * Logout user
     * POST /api/logout
     */
    public function logout(Request $request): JsonResponse
    {
        // Delete current token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout successful'
        ]);
    }

    /**
     * Get user's accessible workspaces based on role
     * GET /api/workspaces
     */
    public function workspaces(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if ($user->has_admin_role) {
            // Admins see all workspaces
            $workspaces = \App\Models\Workspace::with(['owner', 'projects'])
                ->withCount(['users', 'projects'])
                ->get()
                ->map(function ($workspace) {
                    // Add pivot data for admin (acting as owner)
                    $workspace->pivot = (object) ['role' => 'owner'];
                    return $workspace;
                });
        } else {
            // Regular users see only their workspaces
            $workspaces = $user->workspaces()
                ->with(['owner', 'projects'])
                ->withCount(['users', 'projects'])
                ->get();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'workspaces' => $workspaces
            ]
        ]);
    }
}
