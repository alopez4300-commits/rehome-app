<?php

namespace App\Filament\Widgets;

use App\Models\Project;
use App\Services\MyHome\MyHomeService;
use Filament\Widgets\Widget;

class RecentActivityWidget extends Widget
{
    protected static string $view = 'filament.widgets.recent-activity-widget';
    
    protected int | string | array $columnSpan = 'full';

    public function getRecentActivity(): \Illuminate\Support\Collection
    {
        $myHomeService = app(MyHomeService::class);
        $allActivity = collect();
        
        // Get recent activity from all projects
        Project::with('workspace')->get()->each(function ($project) use ($myHomeService, $allActivity) {
            try {
                $activity = $myHomeService->read($project, 5);
                $activity->each(function (&$entry) use ($project) {
                    $entry['project_name'] = $project->name;
                    $entry['workspace_name'] = $project->workspace->name;
                });
                $allActivity = $allActivity->merge($activity);
            } catch (\Exception $e) {
                // Handle any MyHome reading errors gracefully
            }
        });
        
        return $allActivity
            ->sortByDesc('timestamp')
            ->take(15);
    }
}
