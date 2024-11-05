<?php

namespace App\Utils\Filament\Actions;

use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;

class TaskEditActionHelper
{
    public static function edit()
    {
        return EditAction::make()
            ->hidden(fn ($record) => !is_null($record->deleted_at) || $record->dt_is_completed)
            ->label('')
            ->modalHeading(__('portal.tasks.edit'))
            ->extraModalFooterActions([
                Action::make('complete')
                    ->color('success')
                    ->close()
                    ->label(__('portal.complete'))
                    ->hidden(fn($record) => !is_null($record->deleted_at))
                    ->visible(function ($record) {
                        return !$record->dt_is_completed &&
                            ($record->user_id === auth()->id() || $record->user_id === null);
                    })
                    ->action(function ($record, $livewire) {
                        $record->complete();

                        $livewire->dispatch('confetti');
                    }),
                DeleteAction::make()
                    ->color('danger')
                    ->visible(fn($record) => $record->created_by === auth()->id())
                    ->successRedirectUrl(url()->previous())

            ])
            ->form([
                TextInput::make('title')
                    ->label(__('portal.tasks.title'))
                    ->required(),
                Textarea::make('information')
                    ->rows(4)
                    ->label(__('portal.tasks.information')),
                Select::make('user_id')
                    ->label(__('portal.users.contacts'))
                    ->options(User::assignableUsers()->pluck('name', 'id'))
                    ->searchable()
                    ->visible(fn () => !auth()->user()->isUser()),
                DatePicker::make('dt_complete_by')->label(__('portal.complete_by')),
                Toggle::make('is_private')
                    ->label(__('portal.tasks.is_private'))
                    ->visible(fn () => auth()->user()->isUser()),
            ]);
    }
}
