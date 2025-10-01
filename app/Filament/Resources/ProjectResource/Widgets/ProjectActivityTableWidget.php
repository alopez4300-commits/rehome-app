<?php

namespace App\Filament\Resources\ProjectResource\Widgets;

use App\Models\Project;
use App\Services\MyHome\MyHomeService;
use Filament\Widgets\Widget;

class ProjectActivityTableWidget extends Widget
{
    protected static string $view = 'filament.widgets.project-activity';
    
    public ?Project $record = null;
    
    protected int | string | array $columnSpan = 'full';

    public function getViewData(): array
    {
        if (!$this->record) {
            return ['entries' => collect()];
        }

        $myHomeService = app(MyHomeService::class);
        $entries = $myHomeService->read($this->record, 10);

        return [
            'entries' => $entries,
            'project' => $this->record,
        ];
    }
}