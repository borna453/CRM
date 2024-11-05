<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Enums\OnboardingTypes;
use App\Events\UserInvitedEvent;
use App\Filament\Resources\UserResource;
use App\Models\User;
use App\Utils\Filament\Actions\OnboardingActionAttributeHelper;
use Archilex\AdvancedTables\AdvancedTables;
use Archilex\AdvancedTables\Components\PresetView;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\On;
use function GuzzleHttp\default_user_agent;

class ListUsers extends ListRecords
{
    use AdvancedTables;

    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->extraAttributes(fn() => [
                    'class' => OnboardingActionAttributeHelper::glow(OnboardingTypes::ADD_USER->value),
                ]),
        ];
    }

    public function getPresetViews(): array
    {
        return [
            'all' => PresetView::make('all')
                    ->label(__('portal.all'))
                    ->icon('heroicon-o-user-group')
                    ->badge(User::mainCompanyUsers()->IsNotSuperAdminOrOwner()->count())
                    ->favorite()
                    ->default(),
            'deleted' => PresetView::make('deleted')
                    ->label(__('portal.deleted'))
                    ->icon('heroicon-o-archive-box')
                    ->badge(User::mainCompanyUsers()->IsNotSuperAdminOrOwner()->onlyTrashed()->count())
                    ->favorite()
                    ->modifyQueryUsing(function ($query) {
                        $query->IsNotSuperAdminOrOwner()->onlyTrashed();
                    }),
        ];
    }
    public function getListeners(): array
    {
        return [
            'userInvited' => '$refresh',
        ];
    }
}
