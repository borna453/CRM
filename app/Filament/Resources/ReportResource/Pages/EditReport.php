<?php

namespace App\Filament\Resources\ReportResource\Pages;

use App\Filament\Resources\ReportResource;
use App\Jobs\SuggestReportTasksJob;
use App\Traits\RedirectToIndexTrait;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\Action;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Parallax\FilamentComments\Actions\CommentsAction;

class EditReport extends EditRecord
{
    use RedirectToIndexTrait;

    protected static string $resource = ReportResource::class;

    protected static string $view = 'report-edit-page';

    public $tasks = [];

    protected function getHeaderActions(): array
    {
        return [
            Action::make('add_tasks')
                ->visible(fn($record) => $record->user->login_allowed)
                ->action(function ($record, $livewire) {
                    $this->tasks = dispatch_sync(new SuggestReportTasksJob($record->description, $record, auth()->id()));

                    $livewire->dispatch('open-modal', id: 'suggest-tasks-modal');
                    $livewire->dispatch('refreshTaskTable');
                })
                ->label(__('portal.add_tasks'))
                ->requiresConfirmation()
                ->modalDescription(__('portal.confirm_adding_tasks'))
                ->icon('heroicon-o-wrench'),
            Actions\DeleteAction::make(),
            CommentsAction::make()
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $description  = $data['fakedescription'];
        unset($data['fakedescription']);
        if(!is_null($description)){
            $data['description'] = $description;
        }
        return parent::handleRecordUpdate($record, $data);
    }
}
