<?php

namespace App\Services\MyHome;

use App\Models\Project;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class MyHomeQueryService
{
    private MyHomeService $myHomeService;

    public function __construct(MyHomeService $myHomeService)
    {
        $this->myHomeService = $myHomeService;
    }

    /**
     * Get recent activity feed with pagination
     */
    public function getActivityFeed(Project $project, int $limit = 50, int $offset = 0): array
    {
        $allEntries = $this->myHomeService->read($project, 1000); // Get more entries for pagination
        
        $total = $allEntries->count();
        $entries = $allEntries->slice($offset, $limit)->values();
        
        return [
            'entries' => $entries,
            'pagination' => [
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset,
                'has_more' => ($offset + $limit) < $total,
            ]
        ];
    }

    /**
     * Get entries by date range
     */
    public function getByDateRange(Project $project, Carbon $start, Carbon $end): Collection
    {
        return $this->myHomeService->read($project)->filter(function ($entry) use ($start, $end) {
            $entryDate = Carbon::parse($entry['ts'] ?? '');
            return $entryDate->between($start, $end);
        });
    }

    /**
     * Get entries by author
     */
    public function getByAuthor(Project $project, int $userId): Collection
    {
        return $this->myHomeService->read($project)->filter(function ($entry) use ($userId) {
            return ($entry['author'] ?? null) === $userId;
        });
    }

    /**
     * Get project timeline (chronological view)
     */
    public function getTimeline(Project $project, int $limit = 100): Collection
    {
        return $this->myHomeService->read($project, $limit)
            ->sortBy('ts')
            ->values();
    }

    /**
     * Get recent activity summary
     */
    public function getRecentActivitySummary(Project $project, int $days = 7): array
    {
        $since = Carbon::now()->subDays($days);
        $recentEntries = $this->getByDateRange($project, $since, Carbon::now());

        return [
            'period' => "Last {$days} days",
            'total_entries' => $recentEntries->count(),
            'by_kind' => $recentEntries->groupBy('kind')->map->count(),
            'by_author' => $recentEntries->groupBy('author_name')->map->count(),
            'recent_entries' => $recentEntries->take(10)->values(),
        ];
    }

    /**
     * Get project health metrics
     */
    public function getProjectHealth(Project $project): array
    {
        $entries = $this->myHomeService->read($project);
        $now = Carbon::now();
        $lastWeek = $now->copy()->subWeek();
        $lastMonth = $now->copy()->subMonth();

        $recentEntries = $entries->filter(function ($entry) use ($lastWeek) {
            return Carbon::parse($entry['ts'] ?? '')->gte($lastWeek);
        });

        $monthlyEntries = $entries->filter(function ($entry) use ($lastMonth) {
            return Carbon::parse($entry['ts'] ?? '')->gte($lastMonth);
        });

        return [
            'activity_score' => $this->calculateActivityScore($recentEntries),
            'recent_activity' => $recentEntries->count(),
            'monthly_activity' => $monthlyEntries->count(),
            'active_users' => $recentEntries->pluck('author_name')->unique()->count(),
            'task_completion_rate' => $this->calculateTaskCompletionRate($project),
            'time_tracking_consistency' => $this->calculateTimeTrackingConsistency($project),
        ];
    }

    /**
     * Search with advanced filters
     */
    public function advancedSearch(Project $project, array $filters): Collection
    {
        $entries = $this->myHomeService->read($project);

        // Apply filters
        if (isset($filters['query']) && !empty($filters['query'])) {
            $entries = $this->myHomeService->search($project, $filters['query']);
        }

        if (isset($filters['kind']) && !empty($filters['kind'])) {
            $entries = $entries->filter(function ($entry) use ($filters) {
                return ($entry['kind'] ?? '') === $filters['kind'];
            });
        }

        if (isset($filters['author']) && !empty($filters['author'])) {
            $entries = $entries->filter(function ($entry) use ($filters) {
                return ($entry['author'] ?? null) === $filters['author'];
            });
        }

        if (isset($filters['date_from']) && !empty($filters['date_from'])) {
            $dateFrom = Carbon::parse($filters['date_from']);
            $entries = $entries->filter(function ($entry) use ($dateFrom) {
                return Carbon::parse($entry['ts'] ?? '')->gte($dateFrom);
            });
        }

        if (isset($filters['date_to']) && !empty($filters['date_to'])) {
            $dateTo = Carbon::parse($filters['date_to']);
            $entries = $entries->filter(function ($entry) use ($dateTo) {
                return Carbon::parse($entry['ts'] ?? '')->lte($dateTo);
            });
        }

        return $entries->values();
    }

    /**
     * Calculate activity score (0-100)
     */
    private function calculateActivityScore(Collection $entries): int
    {
        $score = 0;
        $entryCount = $entries->count();

        // Base score from entry count
        $score += min($entryCount * 2, 50); // Max 50 points for volume

        // Bonus for variety of entry types
        $uniqueKinds = $entries->pluck('kind')->unique()->count();
        $score += min($uniqueKinds * 5, 25); // Max 25 points for variety

        // Bonus for multiple active users
        $activeUsers = $entries->pluck('author_name')->unique()->count();
        $score += min($activeUsers * 5, 25); // Max 25 points for collaboration

        return min($score, 100);
    }

    /**
     * Calculate task completion rate
     */
    private function calculateTaskCompletionRate(Project $project): float
    {
        $tasks = $this->myHomeService->getTasks($project);
        
        if ($tasks->isEmpty()) {
            return 0.0;
        }

        $completed = $tasks->where('status', 'completed')->count();
        return round(($completed / $tasks->count()) * 100, 1);
    }

    /**
     * Calculate time tracking consistency
     */
    private function calculateTimeTrackingConsistency(Project $project): float
    {
        $timeLogs = $this->myHomeService->getTimeLogs($project);
        
        if ($timeLogs->isEmpty()) {
            return 0.0;
        }

        // Group by date and count days with time entries
        $daysWithTime = $timeLogs->groupBy(function ($entry) {
            return Carbon::parse($entry['ts'])->format('Y-m-d');
        })->count();

        // Calculate consistency based on recent activity
        $recentDays = 7; // Last 7 days
        $consistency = min(($daysWithTime / $recentDays) * 100, 100);

        return round($consistency, 1);
    }

    /**
     * Get file storage statistics
     */
    public function getFileStats(Project $project): array
    {
        $files = $this->myHomeService->getFiles($project);
        
        return [
            'total_files' => $files->count(),
            'total_size' => $files->sum('size'),
            'by_type' => $files->groupBy('type')->map->count(),
            'recent_uploads' => $files->take(10)->values(),
        ];
    }

    /**
     * Get AI usage statistics
     */
    public function getAIStats(Project $project): array
    {
        $aiInteractions = $this->myHomeService->getAIInteractions($project);
        $prompts = $aiInteractions->where('kind', '/ai.prompt');
        $responses = $aiInteractions->where('kind', '/ai.response');

        return [
            'total_prompts' => $prompts->count(),
            'total_responses' => $responses->count(),
            'recent_interactions' => $aiInteractions->take(10)->values(),
            'by_user' => $aiInteractions->groupBy('author_name')->map->count(),
        ];
    }
}
