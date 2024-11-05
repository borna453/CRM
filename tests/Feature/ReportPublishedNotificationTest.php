<?php

use App\Models\Report;
use App\Models\User;
use App\Notifications\ReportPublished;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

it('sends a report published notification when a report is published', function () {
    Notification::fake();

    $report = Report::create([
        'user_id' => $this->regularUser->id,
        'title' => 'Annual Report',
    ]);

    $report->publish();

    Notification::assertSentTo(
        $this->regularUser,
        ReportPublished::class,
        function ($notification) use ($report) {
            return $notification->getReport()->id === $report->id;
        }
    );
});

it('contains correct content in mail notification', function () {
    Notification::fake();

    $report = Report::create([
        'user_id' => $this->regularUser->id,
        'title' => 'Annual report',
    ]);

    $report->publish();

    Notification::assertSentTo($this->regularUser, ReportPublished::class, function ($notification) {
        return Str::contains($notification->getEmailContent(), 'Annual report');
    });
});
