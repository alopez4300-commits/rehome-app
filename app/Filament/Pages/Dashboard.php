<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\RecentActivityWidget;
use App\Filament\Widgets\SiteHealthWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static string $view = 'filament.pages.dashboard';

    public function getWidgets(): array
    {
        return [
            SiteHealthWidget::class,
            RecentActivityWidget::class,
        ];
    }
}