<?php

use App\Filament\Resources\AppointmentResource\Widgets\CalendarWidget;
use App\Models\Appointment;
use Carbon\Carbon;

it('fetches events correctly', function () {
    $this->actingAs($this->adminUser);

    Appointment::create([
        'user_id' => $this->adminUser->id,
        'title' => 'Sample Appointment',
        'description' => 'Sample Description',
        'dt_start' => Carbon::now()->addHours(2),
        'dt_end' => Carbon::now()->addHours(4),
    ]);

    $fetchInfo = [
        'start' => Carbon::now()->toDateString(),
        'end' => Carbon::now()->addDays(1)->toDateString(),
    ];

    $widget = new CalendarWidget();
    $events = $widget->fetchEvents($fetchInfo);

    expect($events)->toBeArray()->toHaveCount(1)
        ->and($events[0]['title'])->toEqual($this->adminUser->name);
});

it('generates correct event mount JS', function () {
    $widget = new CalendarWidget();
    $js = $widget->eventDidMount();

    expect($js)->toContain('function({ event, timeText, isStart, isEnd, isMirror, isPast, isFuture, isToday, el, view }){')
        ->and($js)->toContain('x-tooltip');
});
