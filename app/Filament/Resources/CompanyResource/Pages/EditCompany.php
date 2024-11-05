<?php

namespace App\Filament\Resources\CompanyResource\Pages;

use App\Filament\Resources\CompanyResource;
use App\Traits\RedirectToIndexTrait;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCompany extends EditRecord
{
    use RedirectToIndexTrait;

    protected static string $resource = CompanyResource::class;

    protected static string $view = 'filament.pages.edit-company-page';

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label(__('portal.archive'))->visible(fn($record) => !$record->is_main),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return false;
    }
}
