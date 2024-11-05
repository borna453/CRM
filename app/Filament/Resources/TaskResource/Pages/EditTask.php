<?php

namespace App\Filament\Resources\TaskResource\Pages;

use App\Filament\Resources\TaskResource;
use App\Traits\RedirectToIndexTrait;
use Filament\Actions;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTask extends EditRecord
{
    use RedirectToIndexTrait;

    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('complete')
                ->label(__('portal.complete'))
                ->hidden(fn ($record) => !is_null($record->deleted_at))
                ->visible(function ($record) {
                    return !$record->dt_is_completed;
                })
                ->action(function ($record, $livewire) {
                    $record->complete();

                    $livewire->dispatch('confetti');
                })
                ->extraAttributes([
                    'class' => 'complete-button',
                ]),
            DeleteAction::make()
                ->modalHeading(__('portal.tasks.delete'))
                ->visible(fn ($record) => $record->created_by === auth()->id() || !$record->dt_is_completed),
        ];
    }
}
