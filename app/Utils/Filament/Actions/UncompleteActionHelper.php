<?php

namespace App\Utils\Filament\Actions;

use Filament\Tables\Actions\Action;

class UncompleteActionHelper
{
    public static function uncompleteAction()
    {
        return Action::make('uncomplete')
            ->hidden(fn ($record) => !is_null($record->deleted_at))
            ->visible(function ($record) {
                return $record->dt_is_completed;
            })
            ->action(function ($record) {
                $record->uncomplete();
            })
            ->icon('heroicon-o-x-mark')
            ->iconButton()
            ->extraAttributes([
                'class' => 'uncomplete-button',
            ]);
    }
}


