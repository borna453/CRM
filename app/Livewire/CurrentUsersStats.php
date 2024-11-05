<?php

namespace App\Livewire;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class CurrentUsersStats extends BaseWidget
{
    public function getListeners(): array
    {
        return [
            'echo:user-activity,UserActivity' => '$refresh',
        ];
    }
    protected function getCards(): array
    {
        return [
            Card::make(__('portal.users.active'), User::query()
                ->where('last_activity', '>', now()
                    ->subMinutes(5)
                    ->getTimestamp())
                ->count()),
        ];
    }
}
