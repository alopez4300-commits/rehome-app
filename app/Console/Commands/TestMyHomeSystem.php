<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\User;
use App\Services\MyHome\MyHomeService;
use Illuminate\Console\Command;

class TestMyHomeSystem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:myhome-system';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the MyHome system by creating sample entries';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing MyHome System...');

        $myHomeService = app(MyHomeService::class);
        
        // Get the first project and user
        $project = Project::first();
        $user = User::first();

        if (!$project || !$user) {
            $this->error('No projects or users found. Run the seeder first.');
            return 1;
        }

        $this->info("Using Project: {$project->name} (ID: {$project->id})");
        $this->info("Using User: {$user->name} (ID: {$user->id})");

        // Create sample entries
        $entries = [
            [
                'kind' => 'comment',
                'content' => 'Project kickoff meeting completed. Client approved the initial wireframes.',
            ],
            [
                'kind' => 'status_change',
                'old_status' => 'planning',
                'new_status' => 'active',
            ],
            [
                'kind' => 'file_upload',
                'filename' => 'project-brief.pdf',
                'file_path' => 'assets/documents/project-brief.pdf',
            ],
            [
                'kind' => 'comment',
                'content' => 'Initial designs are ready for client review. Awaiting feedback.',
            ],
            [
                'kind' => 'system',
                'action' => 'project_created',
                'message' => 'Project automatically created from workspace template',
            ],
        ];

        foreach ($entries as $index => $entryData) {
            try {
                if ($entryData['kind'] === 'system') {
                    $entry = $myHomeService->addSystemEntry($project, $entryData);
                } elseif ($entryData['kind'] === 'comment') {
                    $entry = $myHomeService->addComment($project, $user, $entryData['content']);
                } elseif ($entryData['kind'] === 'status_change') {
                    $entry = $myHomeService->addStatusChange(
                        $project, 
                        $user, 
                        $entryData['old_status'], 
                        $entryData['new_status']
                    );
                } elseif ($entryData['kind'] === 'file_upload') {
                    $entry = $myHomeService->addFileUpload(
                        $project, 
                        $user, 
                        $entryData['filename'], 
                        $entryData['file_path']
                    );
                } else {
                    $entry = $myHomeService->append($project, $user, $entryData);
                }

                $this->info("✅ Created {$entryData['kind']} entry: {$entry['id']}");
                
                // Small delay to ensure different timestamps
                usleep(100000); // 0.1 seconds
                
            } catch (\Exception $e) {
                $this->error("❌ Failed to create {$entryData['kind']} entry: {$e->getMessage()}");
            }
        }

        // Read back the entries
        $this->info("\n📖 Reading back entries...");
        $entries = $myHomeService->read($project, 10);
        
        $this->info("Found {$entries->count()} entries:");
        foreach ($entries as $entry) {
            $this->line("  - {$entry['timestamp']}: {$entry['kind']} by {$entry['user_name']}");
        }

        // Check file system
        $myHomePath = "projects/{$project->workspace_id}/{$project->id}/myhome/myhome.ndjson";
        if (\Storage::exists($myHomePath)) {
            $this->info("\n📁 MyHome file created successfully at: storage/app/{$myHomePath}");
            $fileSize = \Storage::size($myHomePath);
            $this->info("File size: {$fileSize} bytes");
        } else {
            $this->error("❌ MyHome file not found at expected location");
        }

        $this->info("\n🎉 MyHome system test completed!");
        $this->info("Visit /system in your browser to see the admin dashboard with activity.");

        return 0;
    }
}
