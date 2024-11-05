<?php

namespace App\Livewire;

use App\Models\User;
use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Contracts\Support\Htmlable;

class Last20UsersTable extends BaseWidget
{
    protected static bool $isLazy = false;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()->last20ActiveUsers()->assignableUsers()
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('portal.name')),
                TextColumn::make('last_activity')
                    ->label(__('portal.users.last_activity'))
                    ->formatStateUsing(function ($state) {
                        return $state ? Carbon::createFromTimestamp($state)->setTimezone('Europe/Amsterdam')->format('d-m-Y H:i:s') : null;
                    })
            ])
            ->paginated(false);
    }

    public function getListeners(): array
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
