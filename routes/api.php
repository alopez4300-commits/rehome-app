<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MyHomeController;
use App\Http\Controllers\Api\AgentController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// MyHome API routes
Route::middleware('auth:sanctum')->group(function () {
    // Project-specific MyHome routes
    Route::prefix('projects/{project}')->group(function () {
        // Activity feed and timeline
        Route::get('/myhome/feed', [MyHomeController::class, 'getFeed']);
        Route::get('/myhome/timeline', [MyHomeController::class, 'getTimeline']);
        
        // Create entries
        Route::post('/myhome/notes', [MyHomeController::class, 'createNote']);
        Route::post('/myhome/tasks', [MyHomeController::class, 'createTask']);
        Route::post('/myhome/time-logs', [MyHomeController::class, 'createTimeLog']);
        
        // Search and filter
        Route::get('/myhome/search', [MyHomeController::class, 'search']);
        Route::get('/myhome/by-kind', [MyHomeController::class, 'getByKind']);
        Route::get('/myhome/by-author', [MyHomeController::class, 'getByAuthor']);
        
        // Statistics and health
        Route::get('/myhome/stats', [MyHomeController::class, 'getStats']);
        Route::get('/myhome/recent-activity', [MyHomeController::class, 'getRecentActivity']);
        Route::get('/myhome/health', [MyHomeController::class, 'getProjectHealth']);
        
        // Specific entry types
        Route::get('/myhome/tasks', [MyHomeController::class, 'getTasks']);
        Route::get('/myhome/time-logs', [MyHomeController::class, 'getTimeLogs']);
        Route::get('/myhome/files', [MyHomeController::class, 'getFiles']);
        Route::get('/myhome/ai-interactions', [MyHomeController::class, 'getAIInteractions']);
        
        // AI Agent routes
        Route::post('/agent/chat', [AgentController::class, 'processRequest']);
        Route::get('/agent/stats', [AgentController::class, 'getProjectStats']);
    });
    
    // User AI statistics
    Route::get('/agent/user-stats', [AgentController::class, 'getUserStats']);
});
