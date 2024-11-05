<?php

namespace App\Filament\Owner\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class CurrentOnlineUsers extends BaseWidget
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
            Card::make(__('portal.users.active'), User::query()->assignableUsers()
                ->whereNotNull('tenant_id')
                ->where('last_activity', '>', now()
                    ->subMinutes(5)
                    ->getTimestamp())
                ->count()),
        ];
    }
}
