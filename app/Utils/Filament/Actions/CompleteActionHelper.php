<?php

namespace App\Utils\Filament\Actions;

use Filament\Tables\Actions\Action;

class CompleteActionHelper
{
    public static function completeAction()
    {
        return Action::make('complete')
            ->hidden(fn($record) => !is_null($record->deleted_at))
            ->visible(function ($record) {
                return !$record->dt_is_completed &&
                    ($record->user_id === auth()->id() || $record->user_id === null);
            })
            ->action(function ($record, $livewire) {
                $record->complete();

                $livewire->dispatch('confetti');
            })
            ->icon('heroicon-o-check')
            ->iconButton()
            ->extraAttributes([
                'class' => 'complete-button',
            ]);
    }
}
