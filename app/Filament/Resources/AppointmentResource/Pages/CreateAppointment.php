<?php

namespace App\Filament\Resources\AppointmentResource\Pages;

use App\Filament\Resources\AppointmentResource;
use App\Traits\DateMutateFieldsTrait;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Http\Request;

class CreateAppointment extends CreateRecord
{
    use DateMutateFieldsTrait;

    protected static string $resource = AppointmentResource::class;

    public $initialView;
    public $initialDate;

    protected function getFormActions(): array
    {
        return [];
    }

    public function mount(): void
    {
        parent::mount();
        $this->initialView = request()->query('view', 'dayGridMonth');
        $this->initialDate = request()->query('date', now()->format('Y-m-d'));
    }

    protected function getFooterWidgets(): array
    {
        return [
            \App\Filament\Resources\AppointmentResource\Widgets\CalendarWidget::make([
                'initialView' => $this->initialView,
                'initialDate' => $this->initialDate,
                'isOnboarding' => \Request::get('onboard_add_appointment') === '1',
            ]),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->combineDateFields($data);
    }
}
