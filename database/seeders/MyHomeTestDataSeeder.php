<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Workspace;
use App\Models\Project;
use App\Services\MyHome\MyHomeService;
use Carbon\Carbon;

class MyHomeTestDataSeeder extends Seeder
{
    private MyHomeService $myHomeService;

    public function __construct(MyHomeService $myHomeService)
    {
        $this->myHomeService = $myHomeService;
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test workspace
        $workspace = Workspace::create([
            'name' => 'Demo Workspace',
            'owner_id' => 1, // Assuming admin user exists
        ]);

        // Create test project
        $project = Project::create([
            'name' => 'MyHome Demo Project',
            'workspace_id' => $workspace->id,
            'status' => 'active',
        ]);

        // Get admin user
        $admin = User::find(1);
        if (!$admin) {
            $this->command->error('Admin user not found. Please create an admin user first.');
            return;
        }

        // Add admin to workspace
        $workspace->users()->attach($admin->id, ['role' => 'owner']);

        // Create sample MyHome entries
        $this->createSampleEntries($project, $admin);

        $this->command->info('MyHome test data created successfully!');
        $this->command->info("Project ID: {$project->id}");
        $this->command->info("Workspace ID: {$workspace->id}");
    }

    private function createSampleEntries(Project $project, User $user): void
    {
        // Project kickoff note
        $this->myHomeService->createNote(
            $project,
            $user,
            'Project kickoff meeting completed. All stakeholders aligned on objectives and timeline.'
        );

        // Task entries
        $this->myHomeService->createTask(
            $project,
            $user,
            'Setup development environment',
            'Configure Laravel, database, and development tools',
            Carbon::now()->addDays(3)->format('Y-m-d'),
            'completed'
        );

        $this->myHomeService->createTask(
            $project,
            $user,
            'Implement MyHome system',
            'Build append-only NDJSON activity logging system',
            Carbon::now()->addDays(7)->format('Y-m-d'),
            'in_progress'
        );

        $this->myHomeService->createTask(
            $project,
            $user,
            'Create API endpoints',
            'Build REST API for MyHome operations',
            Carbon::now()->addDays(10)->format('Y-m-d'),
            'pending'
        );

        // Time log entries
        $this->myHomeService->createTimeLog(
            $project,
            $user,
            2.5,
            'Setup development environment',
            'Initial project setup and configuration'
        );

        $this->myHomeService->createTimeLog(
            $project,
            $user,
            4.0,
            'Implement MyHome system',
            'Core service implementation and testing'
        );

        $this->myHomeService->createTimeLog(
            $project,
            $user,
            1.5,
            'Code review and documentation',
            'Reviewing implementation and updating docs'
        );

        // File upload entries
        $this->myHomeService->createFileEntry(
            $project,
            $user,
            'assets/documents/project-brief.pdf',
            1024000,
            'application/pdf'
        );

        $this->myHomeService->createFileEntry(
            $project,
            $user,
            'assets/images/architecture-diagram.png',
            512000,
            'image/png'
        );

        // AI interaction entries
        $this->myHomeService->createAIPrompt(
            $project,
            $user,
            'What are the key requirements for the MyHome system?'
        );

        $this->myHomeService->createAIResponse(
            $project,
            $user,
            'The MyHome system should provide append-only activity logging with NDJSON format, support for multiple entry types (notes, tasks, time logs, files, AI interactions), and efficient querying capabilities.',
            [
                'provider' => 'claude',
                'model' => 'claude-3-sonnet-20240229',
                'tokens_used' => 150,
                'response_time' => 1250
            ]
        );

        // Status update
        $this->myHomeService->append($project, $user, [
            'kind' => '/status',
            'status' => 'in_progress',
            'message' => 'Development phase started',
            'progress' => 25
        ]);

        // Additional notes
        $this->myHomeService->createNote(
            $project,
            $user,
            'Database schema finalized. Ready to implement core services.'
        );

        $this->myHomeService->createNote(
            $project,
            $user,
            'API endpoints designed and documented. Starting implementation.'
        );

        // More time logs
        $this->myHomeService->createTimeLog(
            $project,
            $user,
            3.0,
            'Database design',
            'Designing and implementing database schema'
        );

        $this->myHomeService->createTimeLog(
            $project,
            $user,
            2.0,
            'API design',
            'Designing REST API endpoints and documentation'
        );

        // Another AI interaction
        $this->myHomeService->createAIPrompt(
            $project,
            $user,
            'How should we handle rate limiting for AI requests?'
        );

        $this->myHomeService->createAIResponse(
            $project,
            $user,
            'Implement rate limiting with configurable limits per user (e.g., 5 requests per minute, 50 per day). Use cache-based tracking with TTL for efficient storage.',
            [
                'provider' => 'openai',
                'model' => 'gpt-4o-mini',
                'tokens_used' => 120,
                'response_time' => 980
            ]
        );

        // Final status update
        $this->myHomeService->append($project, $user, [
            'kind' => '/status',
            'status' => 'in_progress',
            'message' => 'Core services implemented, testing phase',
            'progress' => 60
        ]);
    }
}