<?php

namespace App\Utils\Filament\Actions;

use App\Enums\Permissions;
use Filament\Tables\Actions\Action;

class RestoreActionHelper
{
    public static function restoreAction(): Action
    {
        return Action::make('restore')
            ->label(__('portal.restore'))
            ->icon('heroicon-s-arrow-path')
            ->visible(fn($record) => $record->trashed())
            ->action(fn($record) => $record->restore());
    }
}
