<?php

namespace App\Filament\Pages;

use App\Enums\Permissions;
use App\Livewire\CurrentUsersStats;
use App\Livewire\CurrentUsersTable;
use App\Livewire\Last20UsersTable;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class OnlineUsers extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.online-users';

    protected function getHeaderWidgets(): array
    {
        return [
            CurrentUsersStats::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return [
            'md' => 2,
            'xl' => 2,
        ];
    }

    protected function getColumns(): int|string|array
    {
        return 2;
    }

    protected function getFooterWidgets(): array
    {
        return [
            CurrentUsersTable::class,
            Last20UsersTable::class
        ];
    }

    public static function getNavigationLabel(): string
    {
        return __('portal.users.online');
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Advanced';
    }

    public function getHeading(): string|Htmlable
    {
        return __('portal.users.online');
    }

    public static function canAccess(): bool
    {
        return auth()->user()->hasTenantPermissionTo(Permissions::VIEW_ONLINE_USERS->value);
    }
}
