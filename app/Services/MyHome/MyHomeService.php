<?php

namespace App\Services\MyHome;

use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class MyHomeService
{
    /**
     * Append an entry to a project's MyHome stream
     */
    public function append(Project $project, User $user, array $entry): array
    {
        $timestamp = Carbon::now()->toISOString();
        
        $myHomeEntry = [
            'id' => $this->generateEntryId(),
            'timestamp' => $timestamp,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'project_id' => $project->id,
            'workspace_id' => $project->workspace_id,
            ...$entry, // Merge user-provided data
        ];

        // Ensure project directory structure exists
        $this->ensureProjectStructure($project);

        // Get the MyHome file path
        $filePath = $this->getMyHomePath($project);

        // Append to NDJSON file
        $jsonLine = json_encode($myHomeEntry, JSON_UNESCAPED_SLASHES) . "\n";
        Storage::append($filePath, $jsonLine);

        return $myHomeEntry;
    }

    /**
     * Read entries from a project's MyHome stream
     */
    public function read(Project $project, int $limit = 100): Collection
    {
        $filePath = $this->getMyHomePath($project);

        if (!Storage::exists($filePath)) {
            return collect();
        }

        $content = Storage::get($filePath);
        $lines = array_filter(explode("\n", $content));

        // Get the last N lines (most recent entries)
        $recentLines = array_slice($lines, -$limit);

        return collect($recentLines)
            ->map(fn($line) => json_decode($line, true))
            ->filter()
            ->reverse()
            ->values();
    }

    /**
     * Get entries by kind/type
     */
    public function getByKind(Project $project, string $kind): Collection
    {
        return $this->read($project, 1000) // Read more entries for filtering
            ->where('kind', $kind);
    }

    /**
     * Search entries by query string
     */
    public function search(Project $project, string $query): Collection
    {
        $query = strtolower($query);
        
        return $this->read($project, 1000)
            ->filter(function ($entry) use ($query) {
                $searchText = strtolower(json_encode($entry));
                return str_contains($searchText, $query);
            });
    }

    /**
     * Get recent activity across all accessible projects for a user
     */
    public function getRecentActivity(User $user, int $limit = 50): Collection
    {
        $projects = $user->workspaces()
            ->with('projects')
            ->get()
            ->pluck('projects')
            ->flatten();

        $allEntries = collect();

        foreach ($projects as $project) {
            $entries = $this->read($project, 20);
            $allEntries = $allEntries->concat($entries);
        }

        return $allEntries
            ->sortByDesc('timestamp')
            ->take($limit)
            ->values();
    }

    /**
     * Ensure project directory structure exists
     */
    private function ensureProjectStructure(Project $project): void
    {
        $basePath = $this->getProjectBasePath($project);
        
        Storage::makeDirectory($basePath . '/myhome');
        Storage::makeDirectory($basePath . '/assets');
        Storage::makeDirectory($basePath . '/metadata');
    }

    /**
     * Get the base path for a project
     */
    private function getProjectBasePath(Project $project): string
    {
        return "projects/{$project->workspace_id}/{$project->id}";
    }

    /**
     * Get the MyHome file path for a project
     */
    private function getMyHomePath(Project $project): string
    {
        return $this->getProjectBasePath($project) . '/myhome/myhome.ndjson';
    }

    /**
     * Generate a unique entry ID
     */
    private function generateEntryId(): string
    {
        return 'mh_' . uniqid() . '_' . time();
    }

    /**
     * Add a comment entry
     */
    public function addComment(Project $project, User $user, string $content): array
    {
        return $this->append($project, $user, [
            'kind' => 'comment',
            'content' => $content,
        ]);
    }

    /**
     * Add a file upload entry
     */
    public function addFileUpload(Project $project, User $user, string $filename, string $path): array
    {
        return $this->append($project, $user, [
            'kind' => 'file_upload',
            'filename' => $filename,
            'file_path' => $path,
        ]);
    }

    /**
     * Add a status change entry
     */
    public function addStatusChange(Project $project, User $user, string $oldStatus, string $newStatus): array
    {
        return $this->append($project, $user, [
            'kind' => 'status_change',
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
        ]);
    }

    /**
     * Add a system entry (automated entries)
     */
    public function addSystemEntry(Project $project, array $data): array
    {
        // Create a system user entry with proper User model structure
        $timestamp = Carbon::now()->toISOString();
        
        $myHomeEntry = [
            'id' => $this->generateEntryId(),
            'timestamp' => $timestamp,
            'user_id' => 0,
            'user_name' => 'System',
            'project_id' => $project->id,
            'workspace_id' => $project->workspace_id,
            'kind' => 'system',
            ...$data,
        ];

        // Ensure project directory structure exists
        $this->ensureProjectStructure($project);

        // Get the MyHome file path
        $filePath = $this->getMyHomePath($project);

        // Append to NDJSON file
        $jsonLine = json_encode($myHomeEntry, JSON_UNESCAPED_SLASHES) . "\n";
        Storage::append($filePath, $jsonLine);

        return $myHomeEntry;
    }
}