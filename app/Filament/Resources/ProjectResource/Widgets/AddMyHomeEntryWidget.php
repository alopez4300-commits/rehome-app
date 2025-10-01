<?php

namespace App\Filament\Resources\ProjectResource\Widgets;

use App\Models\Project;
use App\Services\MyHome\MyHomeService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Widgets\Widget;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class AddMyHomeEntryWidget extends Widget
{
    protected static string $view = 'filament.widgets.add-myhome-entry';
    
    public ?Project $record = null;
    
    protected int | string | array $columnSpan = 'full';

    public function addEntryAction(): Action
    {
        return Action::make('addEntry')
            ->label('Add Activity Entry')
            ->icon('heroicon-o-plus')
            ->form([
                Forms\Components\Select::make('kind')
                    ->label('Entry Type')
                    ->options([
                        'comment' => 'Comment',
                        'status_change' => 'Status Change',
                        'file_upload' => 'File Upload',
                        'system' => 'System Entry',
                    ])
                    ->required(),
                Forms\Components\Textarea::make('content')
                    ->label('Content')
                    ->required()
                    ->rows(3),
                Forms\Components\KeyValue::make('metadata')
                    ->label('Additional Data')
                    ->keyLabel('Key')
                    ->valueLabel('Value'),
            ])
            ->action(function (array $data) {
                if (!$this->record) {
                    return;
                }

                $myHomeService = app(MyHomeService::class);
                
                $entry = $myHomeService->append(
                    $this->record,
                    auth()->user(),
                    $data
                );

                Notification::make()
                    ->title('Entry Added')
                    ->body('MyHome entry added successfully')
                    ->success()
                    ->send();

                // Refresh the page to show new entry
                $this->redirect(request()->url());
            });
    }
}