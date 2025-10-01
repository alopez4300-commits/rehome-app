<?php

namespace App\Services\Agent;

use App\Models\Project;
use App\Models\User;
use App\Services\MyHome\MyHomeService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ContextBuilder
{
    private MyHomeService $myHomeService;

    public function __construct(MyHomeService $myHomeService)
    {
        $this->myHomeService = $myHomeService;
    }

    /**
     * Build AI context from MyHome entries and project metadata
     */
    public function buildContext(Project $project, User $user, int $maxTokens = 8000): array
    {
        $context = [
            'project' => $this->getProjectMetadata($project),
            'team' => $this->getTeamMetadata($project),
            'recent_activity' => $this->getRecentActivity($project, $user),
            'tasks' => $this->getTaskSummary($project),
            'time_logs' => $this->getTimeLogSummary($project),
            'files' => $this->getFileSummary($project),
        ];

        // Estimate tokens and truncate if needed
        $estimatedTokens = $this->estimateTokens(json_encode($context));
        
        if ($estimatedTokens > $maxTokens) {
            $context = $this->truncateToFit($context, $maxTokens);
        }

        return $context;
    }

    /**
     * Get project metadata for AI context
     */
    private function getProjectMetadata(Project $project): array
    {
        return [
            'id' => $project->id,
            'name' => $project->name,
            'description' => $project->description,
            'status' => $project->status,
            'start_date' => $project->start_date,
            'end_date' => $project->end_date,
            'created_at' => $project->created_at,
            'workspace' => [
                'id' => $project->workspace->id,
                'name' => $project->workspace->name,
            ],
        ];
    }

    /**
     * Get team metadata for AI context
     */
    private function getTeamMetadata(Project $project): array
    {
        $workspace = $project->workspace;
        $members = $workspace->users()->with('user')->get();

        return [
            'workspace_owner' => [
                'id' => $workspace->owner->id,
                'name' => $workspace->owner->name,
                'email' => $workspace->owner->email,
            ],
            'members' => $members->map(function ($member) {
                return [
                    'id' => $member->user->id,
                    'name' => $member->user->name,
                    'email' => $member->user->email,
                    'role' => $member->pivot->role,
                ];
            })->toArray(),
        ];
    }

    /**
     * Get recent activity for AI context
     */
    private function getRecentActivity(Project $project, User $user): array
    {
        $entries = $this->myHomeService->read($project, 100);
        
        // Filter and redact based on user role
        $filteredEntries = $entries->map(function ($entry) use ($user) {
            return $this->redactPII($entry, $this->getUserRole($user, $project));
        });

        return $filteredEntries->take(50)->values()->toArray();
    }

    /**
     * Get task summary for AI context
     */
    private function getTaskSummary(Project $project): array
    {
        $tasks = $this->myHomeService->getTasks($project);
        
        return [
            'total_tasks' => $tasks->count(),
            'by_status' => $tasks->groupBy('status')->map->count(),
            'recent_tasks' => $tasks->take(20)->values()->toArray(),
            'overdue_tasks' => $tasks->filter(function ($task) {
                if (!isset($task['due'])) return false;
                return \Carbon\Carbon::parse($task['due'])->isPast();
            })->values()->toArray(),
        ];
    }

    /**
     * Get time log summary for AI context
     */
    private function getTimeLogSummary(Project $project): array
    {
        $timeLogs = $this->myHomeService->getTimeLogs($project);
        
        return [
            'total_hours' => $timeLogs->sum('hours'),
            'by_user' => $timeLogs->groupBy('author_name')->map(function ($logs) {
                return [
                    'total_hours' => $logs->sum('hours'),
                    'entries' => $logs->count(),
                ];
            }),
            'recent_logs' => $timeLogs->take(20)->values()->toArray(),
        ];
    }

    /**
     * Get file summary for AI context
     */
    private function getFileSummary(Project $project): array
    {
        $files = $this->myHomeService->getFiles($project);
        
        return [
            'total_files' => $files->count(),
            'total_size' => $files->sum('size'),
            'by_type' => $files->groupBy('type')->map->count(),
            'recent_files' => $files->take(20)->values()->toArray(),
        ];
    }

    /**
     * Truncate context to fit token budget
     */
    public function truncateToFit(array $context, int $tokenBudget): array
    {
        $truncated = $context;
        
        // Priority order: project metadata, team, recent activity, tasks, time logs, files
        $priorities = ['project', 'team', 'recent_activity', 'tasks', 'time_logs', 'files'];
        
        foreach ($priorities as $section) {
            if (!isset($truncated[$section])) continue;
            
            $currentTokens = $this->estimateTokens(json_encode($truncated));
            
            if ($currentTokens <= $tokenBudget) {
                break;
            }
            
            // Truncate this section
            $truncated[$section] = $this->truncateSection($truncated[$section], $section);
        }
        
        return $truncated;
    }

    /**
     * Truncate a specific section
     */
    private function truncateSection($section, string $sectionName): array
    {
        switch ($sectionName) {
            case 'recent_activity':
                if (is_array($section) && count($section) > 20) {
                    return array_slice($section, -20); // Keep last 20 entries
                }
                break;
                
            case 'tasks':
                if (isset($section['recent_tasks']) && count($section['recent_tasks']) > 10) {
                    $section['recent_tasks'] = array_slice($section['recent_tasks'], -10);
                }
                break;
                
            case 'time_logs':
                if (isset($section['recent_logs']) && count($section['recent_logs']) > 10) {
                    $section['recent_logs'] = array_slice($section['recent_logs'], -10);
                }
                break;
                
            case 'files':
                if (isset($section['recent_files']) && count($section['recent_files']) > 10) {
                    $section['recent_files'] = array_slice($section['recent_files'], -10);
                }
                break;
        }
        
        return $section;
    }

    /**
     * Estimate token count for text
     */
    public function estimateTokens(string $text): int
    {
        // Rough estimation: 1 token ≈ 4 characters for English text
        // This is conservative and should be adjusted based on actual usage
        return ceil(strlen($text) / 4);
    }

    /**
     * Redact PII based on user role
     */
    public function redactPII(array $entry, string $userRole): array
    {
        $redacted = $entry;
        
        // Define redaction patterns
        $piiPatterns = [
            'email' => '/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b/',
            'phone' => '/\b\d{3}[-.]?\d{3}[-.]?\d{4}\b/',
            'ssn' => '/\b\d{3}-\d{2}-\d{4}\b/',
        ];
        
        // Define redaction by role
        $redactionByRole = [
            'admin' => [],
            'owner' => [],
            'member' => [],
            'consultant' => ['email', 'phone'],
            'client' => ['email', 'phone', 'ssn'],
        ];
        
        $fieldsToRedact = $redactionByRole[$userRole] ?? ['email', 'phone', 'ssn'];
        
        // Redact text fields
        $textFields = ['text', 'title', 'description', 'prompt', 'content'];
        foreach ($textFields as $field) {
            if (isset($redacted[$field])) {
                $redacted[$field] = $this->redactText($redacted[$field], $piiPatterns, $fieldsToRedact);
            }
        }
        
        return $redacted;
    }

    /**
     * Redact text content
     */
    private function redactText(string $text, array $patterns, array $fieldsToRedact): string
    {
        $redacted = $text;
        
        foreach ($fieldsToRedact as $field) {
            if (isset($patterns[$field])) {
                $redacted = preg_replace($patterns[$field], "[REDACTED]", $redacted);
            }
        }
        
        return $redacted;
    }

    /**
     * Get user role in project workspace
     */
    private function getUserRole(User $user, Project $project): string
    {
        // Admin acts as owner
        if ($user->isAdmin()) {
            return 'admin';
        }
        
        $workspace = $project->workspace;
        $member = $workspace->users()->where('user_id', $user->id)->first();
        
        return $member ? $member->pivot->role : 'client';
    }

    /**
     * Build context for AI agent with Claude and OpenAI backup
     */
    public function buildAgentContext(Project $project, User $user, string $query, int $maxTokens = 8000): array
    {
        $baseContext = $this->buildContext($project, $user, $maxTokens);
        
        return [
            'system_prompt' => $this->buildSystemPrompt($project, $user),
            'context' => $baseContext,
            'query' => $query,
            'user_role' => $this->getUserRole($user, $project),
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * Build system prompt for AI agent
     */
    private function buildSystemPrompt(Project $project, User $user): string
    {
        $userRole = $this->getUserRole($user, $project);
        
        return "You are an AI assistant for the project '{$project->name}'. " .
               "The user has the role: {$userRole}. " .
               "Provide helpful, accurate responses based on the project context. " .
               "Be concise but thorough. " .
               "If you don't have enough information, ask clarifying questions. " .
               "Always maintain a professional and helpful tone.";
    }
}
