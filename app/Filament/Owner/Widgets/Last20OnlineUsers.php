<?php

namespace App\Filament\Owner\Widgets;

use App\Models\User;
use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Contracts\Support\Htmlable;

class Last20OnlineUsers extends BaseWidget
{
    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()->last20ActiveUsers()->whereNotNull('tenant_id')
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
                    })
            ])->paginated(false);
    }

    protected function getListeners()
    {
        return [
            'echo:user-activity,UserActivity' => '$refresh',
        ];
    }

    protected function getTableHeading(): string|Htmlable|null
    {
        return __('portal.users.last_20');
    }
}
