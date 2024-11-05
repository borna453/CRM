<?php

namespace App\Filament\Resources\UnbilledAppointmentsResource\Pages;

use App\Enums\Features;
use App\Enums\Permissions;
use App\Enums\ViewOptions;
use App\Filament\Resources\AppointmentResource;
use App\Filament\Resources\UnbilledAppointmentsResource;
use App\Models\Appointment;
use App\Models\Feature;
use Filament\Actions;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;

class ViewUnbilledAppointments extends ViewRecord implements HasTable
{
    use InteractsWithTable;

    protected static string $view = 'filament.pages.view-unbilled-appointments';

    protected static string $resource = UnbilledAppointmentsResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            \Filament\Infolists\Components\Section::make([
                TextEntry::make('user.company.name')
                    ->label(__('portal.companies.company'))
            ])
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Appointment::query()->shouldBeInvoiced()
                ->with('user')
                ->belongsToCompany($this->record->user->company_id))
            ->columns(AppointmentResource::getColumns(ViewOptions::UNBILLED_APPOINTMENTS))
            ->bulkActions([
                BulkAction::make('invoice')->label(__('portal.invoices.invoice_selected'))->action(function($records){
                    foreach ($records as $record) {
                        $record->invoice();
                    }
                })->requiresConfirmation()->deselectRecordsAfterCompletion()->visible(fn() => auth()->user()->can('editUnbilled', Appointment::class))
            ]);
    }

    protected function getHeaderActions(): array
    {
        $appointments = Appointment::query()->shouldBeInvoiced()
            ->belongsToCompany($this->record->user->company_id)->get();
        return [
            Actions\Action::make('invoice_all')->label(__('portal.invoices.invoice_all'))->action(function() use($appointments){
                foreach ($appointments as $appointment) {
                    $appointment->invoice();
                }
            })->requiresConfirmation()->visible(fn() => auth()->user()->can('editUnbilled', Appointment::class))
        ];
    }

    public function getHeading(): string|Htmlable
    {
        return __('portal.appointments.unbilled_appointments');
    }

    public static function canAccess(array $parameters = []): bool
    {
        return Feature::isActive(Features::ADMINISTRATION) && Feature::isActive(Features::APPOINTMENTS_AND_REPORTS);
    }
}
