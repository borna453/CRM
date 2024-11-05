<?php

namespace App\Filament\Owner\Resources\TenantResource\Pages;

use App\Filament\Owner\Resources\TenantResource;
use App\Models\Appointment;
use App\Models\Company;
use App\Models\PinboardItem;
use App\Models\Feature;
use App\Models\Label;
use App\Models\Media;
use App\Models\Opportunity;
use App\Models\Report;
use App\Models\Task;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ViewTenant extends ViewRecord
{
    protected static string $resource = TenantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('tenant_login_page')
                ->label(__('portal.login'))
                ->icon('heroicon-o-globe-alt')
                ->iconSize('lg')
                ->url(fn($record) => $record->route('login'))
                ->openUrlInNewTab(),
            Action::make('force_delete')
                ->label(__('portal.delete'))
                ->color('danger')
                ->icon('heroicon-o-trash')
                ->action(function ($record) {
                    $record->forceDelete();
                    $this->redirect(route('filament.owner.resources.tenants.index'));
                })
                ->requiresConfirmation()
        ];
    }
}
