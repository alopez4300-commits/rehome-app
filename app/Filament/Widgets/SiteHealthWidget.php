<?php

namespace App\Filament\Widgets;

use App\Models\Project;
use App\Models\User;
use App\Models\Workspace;
use App\Services\MyHome\MyHomeService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Storage;

class SiteHealthWidget extends BaseWidget
{
    protected function getStats(): array
    {
        // Count MyHome files across all projects
        $myHomeFileCount = 0;
        $totalEntries = 0;
        
        try {
            $projectDirs = Storage::directories('projects');
            foreach ($projectDirs as $workspaceDir) {
                $projectSubDirs = Storage::directories($workspaceDir);
                foreach ($projectSubDirs as $projectDir) {
                    $myHomePath = $projectDir . '/myhome/myhome.ndjson';
                    if (Storage::exists($myHomePath)) {
                        $myHomeFileCount++;
                        $content = Storage::get($myHomePath);
                        $lines = array_filter(explode("\n", $content));
                        $totalEntries += count($lines);
                    }
                }
            }
        } catch (\Exception $e) {
            // Handle any storage errors gracefully
        }

        return [
            Stat::make('Total Users', User::count())
                ->description('Registered users')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),
                
            Stat::make('Active Workspaces', Workspace::count())
                ->description('Workspaces created')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('info'),
                
            Stat::make('Total Projects', Project::count())
                ->description('Projects across all workspaces')
                ->descriptionIcon('heroicon-m-folder')
                ->color('warning'),

            Stat::make('MyHome Files', $myHomeFileCount)
                ->description($totalEntries . ' total entries')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),
                
            Stat::make('Admin Users', User::where('has_admin_role', true)->count())
                ->description('Users with admin access')
                ->descriptionIcon('heroicon-m-shield-check')
                ->color('danger'),
        ];
    }
}
