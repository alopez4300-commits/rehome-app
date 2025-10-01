<?php

namespace App\Services\MyHome;

use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MyHomeService
{
    /**
     * Append a new entry to the MyHome activity log
     */
    public function append(Project $project, User $user, array $entry): array
    {
        $entry = array_merge([
            'ts' => Carbon::now()->toISOString(),
            'author' => $user->id,
            'author_name' => $user->name,
        ], $entry);

        $filePath = $this->getMyHomeFilePath($project);
        $directory = dirname($filePath);

        // Ensure directory exists
        if (!Storage::exists($directory)) {
            Storage::makeDirectory($directory);
        }

        // Append to NDJSON file
        $line = json_encode($entry) . "\n";
        Storage::append($filePath, $line);

        Log::info('MyHome entry added', [
            'project_id' => $project->id,
            'user_id' => $user->id,
            'kind' => $entry['kind'] ?? 'unknown'
        ]);

        return $entry;
    }

    /**
     * Read recent entries from MyHome log
     */
    public function read(Project $project, int $limit = 100): Collection
    {
        $filePath = $this->getMyHomeFilePath($project);

        if (!Storage::exists($filePath)) {
            return collect();
        }

        $content = Storage::get($filePath);
        $lines = array_filter(explode("\n", $content));
        
        // Get last N lines (most recent)
        $recentLines = array_slice($lines, -$limit);
        
        return collect($recentLines)->map(function ($line) {
            return json_decode($line, true);
        })->filter()->values();
    }

    /**
     * Get entries by kind (type)
     */
    public function getByKind(Project $project, string $kind): Collection
    {
        return $this->read($project)->filter(function ($entry) use ($kind) {
            return ($entry['kind'] ?? '') === $kind;
        });
    }

    /**
     * Search entries by text content
     */
    public function search(Project $project, string $query): Collection
    {
        return $this->read($project)->filter(function ($entry) use ($query) {
            $searchableText = $this->getSearchableText($entry);
            return stripos($searchableText, $query) !== false;
        });
    }

    /**
     * Get task entries
     */
    public function getTasks(Project $project): Collection
    {
        return $this->getByKind($project, '/task');
    }

    /**
     * Get time log entries
     */
    public function getTimeLogs(Project $project): Collection
    {
        return $this->getByKind($project, '/time');
    }

    /**
     * Get file entries
     */
    public function getFiles(Project $project): Collection
    {
        return $this->getByKind($project, '/file');
    }

    /**
     * Get AI interaction entries
     */
    public function getAIInteractions(Project $project): Collection
    {
        return $this->read($project)->filter(function ($entry) {
            $kind = $entry['kind'] ?? '';
            return in_array($kind, ['/ai.prompt', '/ai.response']);
        });
    }

    /**
     * Get project statistics
     */
    public function getStats(Project $project): array
    {
        $entries = $this->read($project);
        
        $stats = [
            'total_entries' => $entries->count(),
            'by_kind' => $entries->groupBy('kind')->map->count(),
            'total_time_hours' => 0,
            'recent_activity' => $entries->where('ts', '>=', Carbon::now()->subDays(7)->toISOString())->count(),
        ];

        // Calculate total time logged
        $timeEntries = $this->getTimeLogs($project);
        $stats['total_time_hours'] = $timeEntries->sum('hours');

        return $stats;
    }

    /**
     * Get MyHome file path for a project
     */
    private function getMyHomeFilePath(Project $project): string
    {
        return "projects/{$project->workspace_id}/{$project->id}/myhome/myhome.ndjson";
    }

    /**
     * Extract searchable text from an entry
     */
    private function getSearchableText(array $entry): string
    {
        $searchable = [];
        
        // Common text fields
        $textFields = ['text', 'title', 'description', 'prompt', 'content'];
        foreach ($textFields as $field) {
            if (isset($entry[$field])) {
                $searchable[] = $entry[$field];
            }
        }

        // Add kind for context
        if (isset($entry['kind'])) {
            $searchable[] = $entry['kind'];
        }

        return implode(' ', $searchable);
    }

    /**
     * Create a note entry
     */
    public function createNote(Project $project, User $user, string $text): array
    {
        return $this->append($project, $user, [
            'kind' => 'note',
            'text' => $text,
        ]);
    }

    /**
     * Create a task entry
     */
    public function createTask(Project $project, User $user, string $title, ?string $description = null, ?string $due = null, string $status = 'pending'): array
    {
        return $this->append($project, $user, [
            'kind' => '/task',
            'title' => $title,
            'description' => $description,
            'due' => $due,
            'status' => $status,
        ]);
    }

    /**
     * Create a time log entry
     */
    public function createTimeLog(Project $project, User $user, float $hours, string $task, ?string $description = null): array
    {
        return $this->append($project, $user, [
            'kind' => '/time',
            'hours' => $hours,
            'task' => $task,
            'description' => $description,
        ]);
    }

    /**
     * Create a file upload entry
     */
    public function createFileEntry(Project $project, User $user, string $path, int $size, string $type): array
    {
        return $this->append($project, $user, [
            'kind' => '/file',
            'path' => $path,
            'size' => $size,
            'type' => $type,
        ]);
    }

    /**
     * Create an AI prompt entry
     */
    public function createAIPrompt(Project $project, User $user, string $prompt): array
    {
        return $this->append($project, $user, [
            'kind' => '/ai.prompt',
            'prompt' => $prompt,
        ]);
    }

    /**
     * Create an AI response entry
     */
    public function createAIResponse(Project $project, User $user, string $text, ?array $metadata = null): array
    {
        $entry = [
            'kind' => '/ai.response',
            'text' => $text,
        ];

        if ($metadata) {
            $entry['metadata'] = $metadata;
        }

        return $this->append($project, $user, $entry);
    }
}