<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Jobs\UserWelcomeJob;
use App\Traits\RedirectToIndexTrait;
use App\Utils\Filament\Actions\InviteUserActionHelper;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Livewire\Attributes\On;
use STS\FilamentImpersonate\Pages\Actions\Impersonate;

class EditUser extends EditRecord
{
    use RedirectToIndexTrait;

    protected static string $resource = UserResource::class;

    protected static string $view = 'filament.pages.edit-user-page';

    public bool $openCustomerOverview = false;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('invite')
                ->icon('heroicon-o-paper-airplane')
                ->visible(fn($record) => !$record->invited_at && $record->login_allowed)
                ->action(function ($record){
                    UserWelcomeJob::dispatch($record);
                }),
            Action::make('customer_overview')
                ->icon('heroicon-o-list-bullet')
                ->label(__('portal.users.customer_overview'))
                ->visible(fn($record) => $record->hasOpenPinboardItems() || $record->hasPastAppointments())
                ->action(function () {
                    $this->openCustomerOverview();
                }),
            Impersonate::make(),
            Actions\DeleteAction::make(),
        ];
    }

    public function openCustomerOverview(): void
    {
        $this->openCustomerOverview = !$this->openCustomerOverview;
    }

    public function getListeners(): array
    {
        return [
            'echo:user-invited,UserInvitedEvent' => '$refresh',
        ];
    }
}
