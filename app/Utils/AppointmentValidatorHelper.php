<?php

namespace App\Utils;

use App\Rules\UniqueAppointment;
use Illuminate\Validation\ValidationException;
use Closure;

class AppointmentValidatorHelper
{
    public static function validateOverlappingTimes($value, $attribute, Closure $fail, $get, $record = null): void
    {
        $date = $get('date');
        $startTime = $get('start_time');
        $endTime = $get('end_time');
        $appointmentId = $record?->id;

        $validator = validator(compact('date', 'startTime', 'endTime'), [
            'endTime' => [
                'required',
                new UniqueAppointment($startTime, $endTime, $date, $appointmentId),
            ],
        ]);

        try {
            $validator->validate();
        } catch (ValidationException $e) {
            foreach ($e->errors() as $messages) {
                foreach ($messages as $message) {
                    $fail($message);
                }
            }
        }
    }
}
