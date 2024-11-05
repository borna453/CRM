<?php

namespace App\Filament\Resources\ReportResource\Pages;

use App\Filament\Resources\AppointmentResource;
use App\Filament\Resources\ReportResource;
use App\Models\Appointment;
use App\Traits\RedirectToIndexTrait;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\On;

class CreateReport extends CreateRecord
{
    protected static string $resource = ReportResource::class;

    public bool $shouldPublish = false;

    protected function getFormActions(): array
    {
        return [
            $this->getSubmitFormAction(),

            Action::make('create_publish')
                ->label(__('portal.reports.create_publish'))
                ->icon('heroicon-s-eye')
                ->action(function (){
                    $this->shouldPublish = true;
                    $this->create();
                })
                ->successRedirectUrl(ReportResource::getUrl('index')),

            $this->getCancelFormAction(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        if($this->shouldPublish){
            return ReportResource::getUrl('index');
        }

        return ReportResource::getUrl('index') . '?activePresetView=to_publish';
    }

    protected function handleRecordCreation(array $data): Model
    {
        $appointmentId = $data['appointment_id'] ?? null;
        unset($data['appointment_id']);
        $reportDescription = $data['fakedescription'] ?? null;
        unset($data['fakedescription']);

        if (!is_null($reportDescription)){
            $data['description'] = $reportDescription;
        }

        if($this->shouldPublish){
            $data['published_at'] = Carbon::now();
        }

        $report = parent::handleRecordCreation($data);

        if(!is_null($appointmentId)){
            $appointment = Appointment::find($appointmentId);
            $appointment->report_id = $report->id;
            $appointment->save();
        }

        return $report;
    }
}
