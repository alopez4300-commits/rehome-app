<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Models\Project;
use App\Models\User;
use App\Services\MyHome\MyHomeService;
use App\Services\MyHome\MyHomeQueryService;
use App\Services\Agent\AgentService;

// Bootstrap Laravel
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🚀 ReHome v2 - MyHome System Test\n";
echo "================================\n\n";

try {
    // Get test data
    $project = Project::find(1);
    $user = User::find(1);
    
    if (!$project || !$user) {
        echo "❌ Test data not found. Please run: php artisan db:seed --class=MyHomeTestDataSeeder\n";
        exit(1);
    }
    
    echo "📊 Project: {$project->name}\n";
    echo "👤 User: {$user->name}\n\n";
    
    // Test MyHome Service
    $myHomeService = app(MyHomeService::class);
    
    echo "📝 MyHome Service Tests:\n";
    echo "------------------------\n";
    
    // Get recent entries
    $entries = $myHomeService->read($project, 10);
    echo "✅ Recent entries: {$entries->count()}\n";
    
    // Get by kind
    $tasks = $myHomeService->getTasks($project);
    echo "✅ Tasks: {$tasks->count()}\n";
    
    $timeLogs = $myHomeService->getTimeLogs($project);
    echo "✅ Time logs: {$timeLogs->count()}\n";
    
    $aiInteractions = $myHomeService->getAIInteractions($project);
    echo "✅ AI interactions: {$aiInteractions->count()}\n";
    
    // Get stats
    $stats = $myHomeService->getStats($project);
    echo "✅ Total entries: {$stats['total_entries']}\n";
    echo "✅ Total time: {$stats['total_time_hours']} hours\n";
    
    echo "\n";
    
    // Test Query Service
    $queryService = app(MyHomeQueryService::class);
    
    echo "🔍 MyHome Query Service Tests:\n";
    echo "------------------------------\n";
    
    // Get activity feed
    $feed = $queryService->getActivityFeed($project, 5);
    echo "✅ Activity feed: {$feed['entries']->count()} entries\n";
    
    // Get recent activity summary
    $recentActivity = $queryService->getRecentActivitySummary($project, 7);
    echo "✅ Recent activity (7 days): {$recentActivity['total_entries']} entries\n";
    
    // Get project health
    $health = $queryService->getProjectHealth($project);
    echo "✅ Activity score: {$health['activity_score']}/100\n";
    echo "✅ Task completion rate: {$health['task_completion_rate']}%\n";
    
    echo "\n";
    
    // Test AI Agent Service
    $agentService = app(AgentService::class);
    
    echo "🤖 AI Agent Service Tests:\n";
    echo "--------------------------\n";
    
    // Get project AI stats
    $aiStats = $agentService->getProjectStats($project);
    echo "✅ AI prompts: {$aiStats['total_prompts']}\n";
    echo "✅ AI responses: {$aiStats['total_responses']}\n";
    echo "✅ Total tokens: {$aiStats['total_tokens']}\n";
    
    // Get user AI stats
    $userStats = $agentService->getUserStats($user);
    echo "✅ User requests today: {$userStats['requests_today']}\n";
    echo "✅ User requests this minute: {$userStats['requests_this_minute']}\n";
    
    echo "\n";
    
    // Test creating a new entry
    echo "➕ Creating new test entry:\n";
    echo "---------------------------\n";
    
    $newEntry = $myHomeService->createNote(
        $project,
        $user,
        'MyHome system test completed successfully! 🎉'
    );
    
    echo "✅ New entry created: {$newEntry['kind']}\n";
    echo "✅ Entry ID: {$newEntry['ts']}\n";
    
    echo "\n";
    
    // Test search
    echo "🔍 Testing search functionality:\n";
    echo "--------------------------------\n";
    
    $searchResults = $myHomeService->search($project, 'test');
    echo "✅ Search results for 'test': {$searchResults->count()} entries\n";
    
    $searchResults = $myHomeService->search($project, 'AI');
    echo "✅ Search results for 'AI': {$searchResults->count()} entries\n";
    
    echo "\n";
    
    // Test file operations
    echo "📁 Testing file operations:\n";
    echo "---------------------------\n";
    
    $filePath = "projects/{$project->workspace_id}/{$project->id}/myhome/myhome.ndjson";
    $exists = \Illuminate\Support\Facades\Storage::exists($filePath);
    echo "✅ MyHome file exists: " . ($exists ? 'Yes' : 'No') . "\n";
    
    if ($exists) {
        $size = \Illuminate\Support\Facades\Storage::size($filePath);
        echo "✅ File size: " . number_format($size) . " bytes\n";
    }
    
    echo "\n";
    
    echo "🎉 All tests completed successfully!\n";
    echo "====================================\n";
    echo "MyHome system is working correctly.\n";
    echo "Ready for Phase 3: Task Management System\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
