<?php

namespace App\Filament\Pages;

use App\Enums\Permissions;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Pages\SettingsPage;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Facades\FilamentView;
use Throwable;
use function Filament\Support\is_app_url;

abstract class ManageTenantConfig extends SettingsPage
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    abstract protected function group(): ?string;

    protected function fillForm(): void
    {
        $this->callHook('beforeFill');

        $data = tenant()->attributesToArray() ?? [];

        if ($group = $this->group()) {
            $data = $data[$group] ?? [];
        }

        $data = $this->mutateFormDataBeforeFill($data);

        $this->form->fill($data);

        $this->callHook('afterFill');
    }

    public function form(Forms\Form $form): Forms\Form
    {
        return parent::form($form)->model(tenant());
    }

    public function save(): void
    {
        try {
            $this->beginDatabaseTransaction();

            $this->callHook('beforeValidate');

            $data = $this->form->getState();

            $this->callHook('afterValidate');

            $data = $this->mutateFormDataBeforeSave($data);

            $this->callHook('beforeSave');

            $tenant = tenant();

            if ($group = $this->group()) {
                $data = [$group => $data];
            }

            $tenant->update($data);

            $this->callHook('afterSave');

            $this->commitDatabaseTransaction();
        } catch (Halt $exception) {
            $exception->shouldRollbackDatabaseTransaction() ?
                $this->rollBackDatabaseTransaction() :
                $this->commitDatabaseTransaction();

            return;
        } catch (Throwable $exception) {
            $this->rollBackDatabaseTransaction();

            throw $exception;
        }

        $this->rememberData();

        $this->getSavedNotification()?->send();

        if ($redirectUrl = $this->getRedirectUrl()) {
            $this->redirect($redirectUrl, navigate: FilamentView::hasSpaMode() && is_app_url($redirectUrl));
        }
    }

    public function getSaveFormAction(): Action
    {
        if(!auth()->user()->hasTenantPermissionTo(Permissions::EDIT_MANAGE_GENERAL_SETTINGS->value)) {
            return parent::getSaveFormAction()->hidden();
        }

        return parent::getSaveFormAction();
    }
}
