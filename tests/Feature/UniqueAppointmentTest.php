<?php

use App\Models\Appointment;
use App\Models\User;
use App\Rules\UniqueAppointment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

it('allows creating an appointment when there are no existing appointments', function () {
    $startTime = Carbon::now()->addDays()->setTime(10, 0)->format('H:i');
    $endTime = Carbon::now()->addDays()->setTime(11, 0)->format('H:i');
    $date = Carbon::now()->addDays()->format('Y-m-d');

    $validator = validator([
        'start_time' => $startTime,
        'end_time' => $endTime,
        'date' => $date,
    ], [
        'end_time' => [
            'required',
            new UniqueAppointment($startTime, $endTime, $date),
        ],
    ]);

    expect(fn() => $validator->validate())->not->toThrow(ValidationException::class);

    $appointment = Appointment::create([
        'user_id' => $this->regularUser->id,
        'title' => 'Test Appointment',
        'description' => 'Test Description',
        'dt_start' => Carbon::parse("$date $startTime")->toDateTimeString(),
        'dt_end' => Carbon::parse("$date $endTime")->toDateTimeString(),
    ]);

    expect($appointment)->toBeInstanceOf(Appointment::class);
});

it('does not allow creating an appointment that overlaps with an existing appointment', function () {
    $existingStart = Carbon::now()->addDays()->setTime(10, 0)->format('Y-m-d H:i');
    $existingEnd = Carbon::now()->addDays()->setTime(11, 0)->format('Y-m-d H:i');

    Appointment::create([
        'user_id' => $this->regularUser->id,
        'title' => 'Existing Appointment',
        'description' => 'Existing Description',
        'dt_start' => $existingStart,
        'dt_end' => $existingEnd,
    ]);

    $newStart = Carbon::now()->addDays()->setTime(10, 30)->setTimezone('Europe/Amsterdam')->format('H:i');
    $newEnd = Carbon::now()->addDays()->setTime(11, 30)->setTimezone('Europe/Amsterdam')->format('H:i');
    $date = Carbon::now()->addDays()->setTimezone('UTC')->format('Y-m-d H:i:s');

    $validator = Validator::make([
        'start_time' => $newStart,
        'end_time' => $newEnd,
        'date' => $date,
    ], [
        'end_time' => [
            'required',
            new UniqueAppointment($newStart, $newEnd, $date),
        ],
    ]);

    $errors = $validator->errors();

    expect($errors->has('end_time'))->toBeTrue()
        ->and($errors->first('end_time'))->toContain(__('portal.appointments.unique'));
});

it('allows creating an appointment when there is an existing appointment on the same day but not overlapping', function () {
    $existingStart = Carbon::now()->addDays(1)->setTime(8, 0)->format('Y-m-d H:i');
    $existingEnd = Carbon::now()->addDays(1)->setTime(9, 0)->format('Y-m-d H:i');
    Appointment::create([
        'user_id' => $this->regularUser->id,
        'title' => 'Existing Appointment',
        'description' => 'Existing Description',
        'dt_start' => $existingStart,
        'dt_end' => $existingEnd,
    ]);

    $newStart = Carbon::now()->addDays(1)->setTime(10, 0)->setTimezone('Europe/Amsterdam')->format('Y-m-d H:i');
    $newEnd = Carbon::now()->addDays(1)->setTime(11, 0)->setTimezone('Europe/Amsterdam')->format('Y-m-d H:i');

    $appointment = Appointment::create([
        'user_id' => $this->regularUser->id,
        'title' => 'New Appointment',
        'description' => 'New Description',
        'dt_start' => $newStart,
        'dt_end' => $newEnd,
    ]);

    expect($appointment)->toBeInstanceOf(Appointment::class);
});

it('allows creating an appointment that starts at the end of an existing appointment', function () {
    $existingStart = Carbon::now()->addDays(1)->setTime(9, 0)->format('Y-m-d H:i');
    $existingEnd = Carbon::now()->addDays(1)->setTime(10, 0)->format('Y-m-d H:i');
    Appointment::create([
        'user_id' => $this->regularUser->id,
        'title' => 'Existing Appointment',
        'description' => 'Existing Description',
        'dt_start' => $existingStart,
        'dt_end' => $existingEnd,
    ]);

    $newStart = Carbon::now()->addDays(1)->setTime(10, 0)->setTimezone('Europe/Amsterdam')->format('Y-m-d H:i');
    $newEnd = Carbon::now()->addDays(1)->setTime(11, 0)->setTimezone('Europe/Amsterdam')->format('Y-m-d H:i');

    $appointment = Appointment::create([
        'user_id' => $this->regularUser->id,
        'title' => 'New Appointment',
        'description' => 'New Description',
        'dt_start' => $newStart,
        'dt_end' => $newEnd,
    ]);

    expect($appointment)->toBeInstanceOf(Appointment::class);
});

it('allows creating an appointment that ends at the start of an existing appointment', function () {
    $existingStart = Carbon::now()->addDays(1)->setTime(10, 0)->format('Y-m-d H:i');
    $existingEnd = Carbon::now()->addDays(1)->setTime(11, 0)->format('Y-m-d H:i');
    Appointment::create([
        'user_id' => $this->regularUser->id,
        'title' => 'Existing Appointment',
        'description' => 'Existing Description',
        'dt_start' => $existingStart,
        'dt_end' => $existingEnd,
    ]);

    $newStart = Carbon::now()->addDays(1)->setTime(9, 0)->setTimezone('Europe/Amsterdam')->format('Y-m-d H:i');
    $newEnd = Carbon::now()->addDays(1)->setTime(10, 0)->setTimezone('Europe/Amsterdam')->format('Y-m-d H:i');

    $appointment = Appointment::create([
        'user_id' => $this->regularUser->id,
        'title' => 'New Appointment',
        'description' => 'New Description',
        'dt_start' => $newStart,
        'dt_end' => $newEnd,
    ]);

    expect($appointment)->toBeInstanceOf(Appointment::class);
});

it('does not allow creating an appointment that overlaps with an existing appointment in the middle', function () {
    $existingStart = Carbon::now()->addDays(1)->setTime(10, 0)->format('Y-m-d H:i');
    $existingEnd = Carbon::now()->addDays(1)->setTime(11, 0)->format('Y-m-d H:i');
    Appointment::create([
        'user_id' => $this->regularUser->id,
        'title' => 'Existing Appointment',
        'description' => 'Existing Description',
        'dt_start' => $existingStart,
        'dt_end' => $existingEnd,
    ]);

    $newStart = Carbon::now()->addDays(1)->setTime(9, 30)->setTimezone('Europe/Amsterdam')->format('H:i');
    $newEnd = Carbon::now()->addDays(1)->setTime(11, 30)->setTimezone('Europe/Amsterdam')->format('H:i');
    $date = Carbon::now()->addDays()->format('Y-m-d H:i:s');

    $validator = Validator::make([
        'start_time' => $newStart,
        'end_time' => $newEnd,
        'date' => $date,
    ], [
        'end_time' => [
            'required',
            new \App\Rules\UniqueAppointment(
                $newStart,
                $newEnd,
                $date
            ),
        ],
    ]);

    $errors = $validator->errors();

    expect($errors->has('end_time'))->toBeTrue()
        ->and($errors->first('end_time'))->toContain(__('portal.appointments.unique'));
});

it('does not allow creating an appointment that is fully within an existing longer appointment', function () {
    $existingStart = Carbon::now()->addDays(1)->setTime(9, 0)->format('Y-m-d H:i');
    $existingEnd = Carbon::now()->addDays(1)->setTime(12, 0)->format('Y-m-d H:i');
    Appointment::create([
        'user_id' => $this->regularUser->id,
        'title' => 'Existing Appointment',
        'description' => 'Existing Description',
        'dt_start' => $existingStart,
        'dt_end' => $existingEnd,
    ]);

    $newStart = Carbon::now()->addDays(1)->setTime(10, 00)->setTimezone('Europe/Amsterdam')->format('H:i');
    $newEnd = Carbon::now()->addDays(1)->setTime(11, 00)->setTimezone('Europe/Amsterdam')->format('H:i');
    $date = Carbon::now()->addDays()->format('Y-m-d H:i:s');

    $validator = Validator::make([
        'start_time' => $newStart,
        'end_time' => $newEnd,
        'date' => $date,
    ], [
        'end_time' => [
            'required',
            new \App\Rules\UniqueAppointment(
                $newStart,
                $newEnd,
                $date
            ),
        ],
    ]);

    $errors = $validator->errors();

    expect($errors->has('end_time'))->toBeTrue()
        ->and($errors->first('end_time'))->toContain(__('portal.appointments.unique'));
});

it('does not allow creating an appointment that overlaps with the start of an existing appointment', function () {
    $existingStart = Carbon::now()->addDays(1)->setTime(10, 0)->format('Y-m-d H:i');
    $existingEnd = Carbon::now()->addDays(1)->setTime(11, 0)->format('Y-m-d H:i');
    Appointment::create([
        'user_id' => $this->regularUser->id,
        'title' => 'Existing Appointment',
        'description' => 'Existing Description',
        'dt_start' => $existingStart,
        'dt_end' => $existingEnd,
    ]);

    $newStart = Carbon::now()->addDays(1)->setTime(9, 30)->setTimezone('Europe/Amsterdam')->format('H:i');
    $newEnd = Carbon::now()->addDays(1)->setTime(10, 30)->setTimezone('Europe/Amsterdam')->format('H:i');
    $date = Carbon::now()->addDays()->format('Y-m-d H:i:s');

    $validator = Validator::make([
        'start_time' => $newStart,
        'end_time' => $newEnd,
        'date' => $date,
    ], [
        'end_time' => [
            'required',
            new \App\Rules\UniqueAppointment(
                $newStart,
                $newEnd,
                $date
            ),
        ],
    ]);

    $errors = $validator->errors();

    expect($errors->has('end_time'))->toBeTrue()
        ->and($errors->first('end_time'))->toContain(__('portal.appointments.unique'));
});

it('does not allow creating an appointment that overlaps with the end of an existing appointment', function () {
    $existingStart = Carbon::now()->addDays(1)->setTime(10, 0)->format('Y-m-d H:i');
    $existingEnd = Carbon::now()->addDays(1)->setTime(11, 0)->format('Y-m-d H:i');
    Appointment::create([
        'user_id' => $this->regularUser->id,
        'title' => 'Existing Appointment',
        'description' => 'Existing Description',
        'dt_start' => $existingStart,
        'dt_end' => $existingEnd,
    ]);

    $newStart = Carbon::now()->addDays(1)->setTime(10, 30)->setTimezone('Europe/Amsterdam')->format('H:i');
    $newEnd = Carbon::now()->addDays(1)->setTime(11, 30)->setTimezone('Europe/Amsterdam')->format('H:i');
    $date = Carbon::now()->addDays()->format('Y-m-d H:i:s');

    $validator = Validator::make([
        'start_time' => $newStart,
        'end_time' => $newEnd,
        'date' => $date,
    ], [
        'end_time' => [
            'required',
            new \App\Rules\UniqueAppointment(
                $newStart,
                $newEnd,
                $date
            ),
        ],
    ]);

    $errors = $validator->errors();

    expect($errors->has('end_time'))->toBeTrue()
        ->and($errors->first('end_time'))->toContain(__('portal.appointments.unique'));
});

it('does not allow creating an appointment that partially overlaps with an existing appointment', function () {
    $existingStart = Carbon::now()->addDays(1)->setTime(10, 0)->format('Y-m-d H:i');
    $existingEnd = Carbon::now()->addDays(1)->setTime(12, 0)->format('Y-m-d H:i');
    Appointment::create([
        'user_id' => $this->regularUser->id,
        'title' => 'Existing Appointment',
        'description' => 'Existing Description',
        'dt_start' => $existingStart,
        'dt_end' => $existingEnd,
    ]);

    $newStart = Carbon::now()->addDays(1)->setTime(11, 00)->setTimezone('Europe/Amsterdam')->format('H:i');
    $newEnd = Carbon::now()->addDays(1)->setTime(13, 00)->setTimezone('Europe/Amsterdam')->format('H:i');
    $date = Carbon::now()->addDays()->format('Y-m-d H:i:s');

    $validator = Validator::make([
        'start_time' => $newStart,
        'end_time' => $newEnd,
        'date' => $date,
    ], [
        'end_time' => [
            'required',
            new \App\Rules\UniqueAppointment(
                $newStart,
                $newEnd,
                $date
            ),
        ],
    ]);

    $errors = $validator->errors();

    expect($errors->has('end_time'))->toBeTrue()
        ->and($errors->first('end_time'))->toContain(__('portal.appointments.unique'));
});

it('does not allow creating an appointment that overlaps the start and end of an existing appointment', function () {
    $existingStart = Carbon::now()->addDays(1)->setTime(9, 0)->format('Y-m-d H:i');
    $existingEnd = Carbon::now()->addDays(1)->setTime(10, 0)->format('Y-m-d H:i');
    Appointment::create([
        'user_id' => $this->regularUser->id,
        'title' => 'Existing Appointment',
        'description' => 'Existing Description',
        'dt_start' => $existingStart,
        'dt_end' => $existingEnd,
    ]);

    $newStart = Carbon::now()->addDays(1)->setTime(8, 45)->setTimezone('Europe/Amsterdam')->format('H:i');
    $newEnd = Carbon::now()->addDays(1)->setTime(10, 15)->setTimezone('Europe/Amsterdam')->format('H:i');
    $date = Carbon::now()->addDays()->format('Y-m-d H:i:s');

    $validator = Validator::make([
        'start_time' => $newStart,
        'end_time' => $newEnd,
        'date' => $date,
    ], [
        'end_time' => [
            'required',
            new \App\Rules\UniqueAppointment(
                $newStart,
                $newEnd,
                $date
            ),
        ],
    ]);

    $errors = $validator->errors();

    expect($errors->has('end_time'))->toBeTrue()
        ->and($errors->first('end_time'))->toContain(__('portal.appointments.unique'));
});

it('allows creating an appointment at the same time for a different user', function () {
    $existingStart = Carbon::now()->addDays(1)->setTime(9, 0)->format('Y-m-d H:i');
    $existingEnd = Carbon::now()->addDays(1)->setTime(10, 0)->format('Y-m-d H:i');
    Appointment::create([
        'user_id' => $this->regularUser->id,
        'title' => 'Existing Appointment',
        'description' => 'Existing Description',
        'dt_start' => $existingStart,
        'dt_end' => $existingEnd,
    ]);

    $newStart = Carbon::now()->addDays(1)->setTime(9, 0)->format('Y-m-d H:i');
    $newEnd = Carbon::now()->addDays(1)->setTime(10, 0)->format('Y-m-d H:i');

    $newUser = User::factory()->create();

    $appointment = Appointment::create([
        'user_id' => $newUser->id,
        'title' => 'New Appointment',
        'description' => 'New Description',
        'dt_start' => $newStart,
        'dt_end' => $newEnd,
    ]);

    expect($appointment)->toBeInstanceOf(Appointment::class);
});
