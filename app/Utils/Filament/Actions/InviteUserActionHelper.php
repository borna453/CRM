<?php

namespace App\Utils\Filament\Actions;

use App\Jobs\UserWelcomeJob;
use Filament\Tables\Actions\Action;

class InviteUserActionHelper
{
    public static function inviteAction(): Action
    {
        return Action::make('invite')
            ->icon('heroicon-o-paper-airplane')
            ->visible(fn($record) => !$record->invited_at && $record->login_allowed && $record->email_enabled)
            ->action(function ($record, $livewire){
                $record->invite();
                UserWelcomeJob::dispatch($record);
                $livewire->dispatch('userInvited');
            })->label(__('portal.companies.invite'));
    }
}
