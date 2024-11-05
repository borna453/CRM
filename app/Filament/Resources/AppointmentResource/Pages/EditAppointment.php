<?php

namespace App\Filament\Resources\AppointmentResource\Pages;

use App\Filament\Resources\AppointmentResource;
use App\Traits\DateMutateFieldsTrait;
use App\Traits\RedirectToIndexTrait;
use Carbon\Carbon;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAppointment extends EditRecord
{
    use DateMutateFieldsTrait;
    use RedirectToIndexTrait;

    protected static string $resource = AppointmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (isset($data['dt_start'])) {
            $startDateTime = new Carbon($data['dt_start']);
            $data['date'] = $startDateTime->toDateString();
            $data['start_time'] = $startDateTime->setTimezone(auth()->user()->timezone)->toTimeString();
        }

        if (isset($data['dt_end'])) {
            $endDateTime = new Carbon($data['dt_end']);
            $data['end_time'] = $endDateTime->setTimezone(auth()->user()->timezone)->toTimeString();
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->combineDateFields($data);
    }
}
