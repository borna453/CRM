<?php

namespace App\Observers;

use App\Models\Appointment;
use App\Models\Report;
use App\Models\Task;
use App\Notifications\AppointmentCreated;
use App\Notifications\AppointmentUpdatedNotification;
use App\Notifications\AppointmentDeletedNotification;
use App\Utils\Filament\FormFields\RichEditorAttachmentsHelper;
use Filament\Notifications\Events\DatabaseNotificationsSent;
use Illuminate\Notifications\DatabaseNotification;

class AppointmentObserver
{
    public function creating(Appointment $appointment): void
    {
        $appointment->created_by = auth()->id();

        if($appointment->description){
            $appointment->description = RichEditorAttachmentsHelper::processContent($appointment?->description);
        }
        if ($appointment->internal_notes) {
            $appointment->internal_notes = RichEditorAttachmentsHelper::processContent($appointment->internal_notes);
        }
    }

    public function created(Appointment $appointment): void
    {
        if($appointment->dt_start->isFuture()) {
            $appointment->user->notify(new AppointmentCreated($appointment));
            auth()->user()->notify(new AppointmentCreated($appointment));

            event(new DatabaseNotificationsSent($appointment->user));
            event(new DatabaseNotificationsSent(auth()->user()));
        }
    }

    public function updated(Appointment $appointment): void
    {
        if ($appointment->isDirty(['dt_start', 'dt_end', 'location'])) {
            if ($appointment->dt_start->isFuture()) {
                $appointment->user->notify(new AppointmentUpdatedNotification($appointment, $appointment->getOriginal('dt_start')));
                auth()->user()->notify(new AppointmentUpdatedNotification($appointment, $appointment->getOriginal('dt_start')));

                event(new DatabaseNotificationsSent($appointment->user));
                event(new DatabaseNotificationsSent(auth()->user()));
            }
            elseif($appointment->dt_start->isPast()){
                $appointment->report->date = $appointment->dt_start;
                $appointment->report->save();
            }
        }
    }

    public function updating(Appointment $appointment): void
    {
        if ($appointment->isDirty('report_id') && $appointment->report_id) {
            Task::where('model_type', Report::class)
                ->where('model_id', $appointment->report_id)
                ->update([
                    'model_type' => Appointment::class,
                    'model_id' => $appointment->id,
                ]);
        }
    }

    public function deleting(Appointment $appointment): void
    {
        DatabaseNotification::where('data->viewData->model_type', Appointment::class)
            ->where('data->viewData->model_id', $appointment->id)
            ->delete();

        if($appointment->dt_start->isFuture()){
            $appointment->user->notify(new AppointmentDeletedNotification($appointment));
            auth()->user()->notify(new AppointmentDeletedNotification($appointment));
        }
    }
}
