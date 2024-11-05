<?php

namespace App\Filament\Resources\AppointmentResource\Pages;

use App\Filament\Resources\AppointmentResource;
use App\Utils\PresetViews\AppointmentPresetViewsHelper;
use Archilex\AdvancedTables\AdvancedTables;
use Filament\Resources\Pages\ListRecords;

class ListAppointments extends ListRecords
{
    use AdvancedTables;

    protected static string $resource = AppointmentResource::class;

    public function getPresetViews(): array
    {
        return AppointmentPresetViewsHelper::appointmentPresetViews();
    }
}
