<?php

use App\Models\Appointment;
use App\Models\User;
use App\Notifications\AppointmentCreated;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

it('sends an appointment created notification when an appointment is created', function () {
    Notification::fake();

    $user = User::factory()->create();
    $appointment = Appointment::create([
        'user_id' => $this->regularUser->id,
        'title' => 'Annual Appointment',
        'description' => 'Annual appointment description',
        'dt_start' => now()->addHours(),
        'dt_end' => now()->addHours(2),
    ]);

    $appointment->save();

    Notification::assertSentTo(
        $this->regularUser,
        AppointmentCreated::class,
        function ($notification) use ($appointment) {
            return $notification->getAppointment()->id === $appointment->id;
        }
    );
});

it('contains correct content in mail notification for an appointment', function () {
    Notification::fake();

    $user = User::factory()->create();

    $appointment = Appointment::create([
        'user_id' => $user->id,
        'title' => 'Annual Appointment',
        'description' => 'Annual appointment description',
        'dt_start' => now()->addHours(),
        'dt_end' => now()->addHours(2),
    ]);

    $appointment->save();

    Notification::assertSentTo($user, AppointmentCreated::class, function ($notification) use ($user) {
        $mailMessage = $notification->toMail($user);

        $hasRawAttachment = !empty($mailMessage->rawAttachments);

        $hasCorrectMarkdown = $mailMessage->markdown === 'emails.dynamic_notification';

        $hasCorrectContent = $notification->getEmailContent();

        return $hasRawAttachment && $hasCorrectMarkdown && $hasCorrectContent;
    });
});

