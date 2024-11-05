<?php

namespace App\Utils\Filament\Actions;

use Filament\Tables\Actions\Action;

class PinboardItemsActionHelper
{
    public static function completeAction(): Action
    {
        return Action::make('complete')
            ->hidden(fn($record) => !is_null($record->deleted_at))
            ->visible(fn($record) => !$record->dt_is_completed)
            ->action(function ($record, $livewire) {
                $record->complete();

                $livewire->dispatch('refreshPinboardItems');
                $livewire->dispatch('confetti');
            })
            ->icon('heroicon-o-check')
            ->iconButton()
            ->extraAttributes([
                'class' => 'complete-button',
            ]);
    }

    public static function uncompleteAction(): Action
    {
        return Action::make('uncomplete')
            ->hidden(fn($record) => !is_null($record->deleted_at))
            ->visible(fn($record) => $record->dt_is_completed)
            ->action(function ($record, $livewire) {
                $record->uncomplete();
                $livewire->dispatch('refreshPinboardItems');
            })
            ->icon('heroicon-o-x-mark')
            ->iconButton()
            ->extraAttributes([
                'class' => 'uncomplete-button',
            ]);
    }
}
