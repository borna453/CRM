<?php

namespace App\Filament\Resources;

use App\Enums\Features;
use App\Enums\Permissions;
use App\Enums\ViewOptions;
use App\Filament\Resources\UnbilledAppointmentsResource\Pages;
use App\Models\Appointment;
use App\Models\Feature;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class UnbilledAppointmentsResource extends Resource
{
    protected static ?string $model = Appointment::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    public static function table(Table $table): Table
    {
        return $table
            ->query(Appointment::query()->shouldBeInvoiced()->with('user.company'))
            ->columns(AppointmentResource::getColumns(ViewOptions::UNBILLED_APPOINTMENTS))
            ->defaultGroup('user.company.name')
            ->recordUrl(function($record){
                return UnbilledAppointmentsResource::getUrl('view', ['record' => $record]);
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUnbilledAppointments::route('/'),
            'view' => Pages\ViewUnbilledAppointments::route('/{record}'),
        ];
    }

    public static function getLabel(): ?string
    {
        return __('portal.invoices.to_invoice');
    }

    public static function getPluralModelLabel(): string
    {
        return __('portal.invoices.to_invoice');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('portal.administration');
    }

    public static function canViewAny(): bool
    {
        return Feature::isActive(Features::ADMINISTRATION) && Feature::isActive(Features::APPOINTMENTS_AND_REPORTS) && auth()->user()->can('viewUnbilled');
    }
}
