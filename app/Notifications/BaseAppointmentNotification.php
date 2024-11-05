<?php

namespace App\Notifications;

use App\Models\Appointment;
use App\Utils\Notifications\AppointmentEventHelper;

abstract class BaseAppointmentNotification extends BaseNotification
{
    public function __construct(
        protected Appointment $appointment
    ) {
        parent::__construct(['appointment' => $this->appointment]);
    }

    protected function getLocationDetails(): mixed
    {
        $address = AppointmentEventHelper::getLocationDetails($this->appointment, $this->appointment->location);

        return $address;
    }

    protected function getDescription(): string
    {
        $description = strip_tags($this->appointment->description);

        if ($this->appointment->location === 'online' && !empty($this->appointment->online_url)) {
            $description .= "\n\n".__('portal.appointments.online_url').": ".$this->appointment->online_url;
        }

        return $description;
    }
}
