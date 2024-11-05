<?php

use App\Filament\Resources\AppointmentResource\Widgets\CalendarWidget;
use App\Models\Appointment;
use Carbon\Carbon;

beforeEach(function () {
    $this->appointmentWithUser = Appointment::create([
        'user_id' => $this->regularUser->id,
        'title' => 'Test Appointment',
        'description' => 'Test Description',
        'dt_start' => Carbon::now()->addDays()->setTime(9, 0)->format('Y-m-d H:i:s'),
        'dt_end' => Carbon::now()->addDays()->setTime(10, 0)->format('Y-m-d H:i:s')
    ]);
});

it('does not display appointments for soft-deleted users in the calendar widget', function () {
    $this->actingAs($this->adminUser);


    $fetchInfo = [
        'start' => Carbon::now()->toDateString(),
        'end' => Carbon::now()->addDays(2)->toDateString(),
    ];

    $widget = new CalendarWidget();
    $events = $widget->fetchEvents($fetchInfo);

    expect($events)->toBeArray()->toHaveCount(1);

    $this->regularUser->delete();

    $events = $widget->fetchEvents($fetchInfo);
    expect($events)->toBeArray()->toHaveCount(0);
});

it('displays appointments correctly when users are restored after being soft-deleted', function () {
    $this->actingAs($this->adminUser);

    $fetchInfo = [
        'start' => Carbon::now()->toDateString(),
        'end' => Carbon::now()->addDays(2)->toDateString(),
    ];

    $widget = new CalendarWidget();
    $events = $widget->fetchEvents($fetchInfo);
    expect($events)->toBeArray()->toHaveCount(1);

    $this->regularUser->delete();
    $events = $widget->fetchEvents($fetchInfo);
    expect($events)->toBeArray()->toHaveCount(0);

    $this->regularUser->restore();

    $events = $widget->fetchEvents($fetchInfo);

    expect($events)->toBeArray()->toHaveCount(1)
        ->and($events[0]['id'])->toEqual($this->appointmentWithUser->id);
});
