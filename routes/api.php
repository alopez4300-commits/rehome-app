<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MyHomeController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// MyHome API routes
Route::middleware('auth:sanctum')->group(function () {
    // Project-specific MyHome routes
    Route::prefix('projects/{project}')->group(function () {
        Route::get('/myhome', [MyHomeController::class, 'index']);
        Route::post('/myhome', [MyHomeController::class, 'store']);
        Route::get('/myhome/search', [MyHomeController::class, 'search']);
        Route::post('/myhome/comment', [MyHomeController::class, 'addComment']);
        Route::post('/myhome/status', [MyHomeController::class, 'addStatusChange']);
    });

    // Global activity feed
    Route::get('/myhome/activity', [MyHomeController::class, 'activity']);
});
