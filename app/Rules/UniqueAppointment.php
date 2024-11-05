<?php

namespace App\Rules;

use App\Models\Appointment;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class UniqueAppointment implements ValidationRule
{
    protected $startTime;
    protected $endTime;
    protected $date;
    protected $currentAppointmentId;

    public function __construct($startTime, $endTime, $date, $currentAppointmentId = null)
    {
        $this->startTime = $startTime;
        $this->endTime = $endTime;
        $this->date = $date;
        $this->currentAppointmentId = $currentAppointmentId;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($this->date)) {
            return;
        }

        $date = Carbon::createFromFormat('Y-m-d H:i:s', $this->date)?->format('Y-m-d');

        $startDateTime = Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $this->startTime, auth()->user()->timezone)?->setTimezone('UTC')->toDateTimeString();
        $endDateTime = Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $this->endTime, auth()->user()->timezone)?->setTimezone('UTC')->toDateTimeString();

        $query = Appointment::where(function($query) use ($startDateTime, $endDateTime) {
                $query->where(function($subQuery) use ($startDateTime, $endDateTime) {
                    $subQuery->where('dt_start', '<', $endDateTime)
                        ->where('dt_end', '>', $startDateTime);
                });
            });
        if ($this->currentAppointmentId){
            $query->where('id', '!=', $this->currentAppointmentId);
        }

        if ($query->exists()) {
            $fail(__('portal.appointments.unique'));
        }
    }
}
