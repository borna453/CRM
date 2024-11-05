<?php

use App\Console\Commands\DailyTasksReminder;
use App\Console\Commands\GetWeeklyUpcomingAppointmentsCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();
Artisan::command(GetWeeklyUpcomingAppointmentsCommand::class , function(){
    $this->comment('Tasks & appointments reminder command executed');
})->mondays()->at('07:30');
Artisan::command(DailyTasksReminder::class, function (){
    $this->comment('Check task deadlines command executed');
})->daily();
