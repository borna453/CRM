<?php

namespace App\Filament\Owner\Widgets;

use App\Models\User;
use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Contracts\Support\Htmlable;
use STS\FilamentImpersonate\Tables\Actions\Impersonate;

class CurrentUsersList extends BaseWidget
{
    protected static bool $isLazy = false;


    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()->whereNotNull('tenant_id')->where('last_activity', '>', now()->subMinutes(5)->getTimestamp())
            )
            ->columns([
                TextColumn::make('tenant_id')
                    ->label(__('portal.tenant.tenant'))
                    ->formatStateUsing(fn($record) => $record->tenant_id),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('portal.name')),

                TextColumn::make('last_activity')
                    ->label(__('portal.users.last_activity'))
                    ->formatStateUsing(function ($state) {
                        return $state ? Carbon::createFromTimestamp($state)->setTimezone('Europe/Amsterdam')->format('d-m-Y H:i:s') : null;
                    })->action(Impersonate::make()),
            ]);
    }

    protected function getListeners()
    {
        return [
            'echo:user-activity,UserActivity' => '$refresh',
        ];
    }

    protected function getTableHeading(): string|Htmlable|null
    {
        return __('portal.users.active_table');
    }
}
