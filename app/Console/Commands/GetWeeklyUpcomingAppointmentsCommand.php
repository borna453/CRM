<?php

namespace App\Console\Commands;

use App\Mail\WeeklyAppointmentReminderMail;
use App\Models\Appointment;
use App\Models\Task;
use App\Models\User;
use App\Notifications\WeeklyAppointmentsAndTasksReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class GetWeeklyUpcomingAppointmentsCommand extends Command
{
    protected $signature = 'get:weekly-upcoming-appointments-tasks';

    protected $description = 'Gets the weekly upcoming appointments and tasks for each user and sends them an email reminder.';

    public function handle(): void
    {
        $appointments = Appointment::query()->with('user')->orderBy('dt_start')
            ->where('dt_start', '>=', now()->startOfWeek())
            ->where('dt_end', '<=', now()->endOfWeek())
            ->get()
            ->groupBy('user_id');

        $tasks = Task::query()->with('user')->orderBy('dt_complete_by')
            ->where('dt_complete_by', '>=', now()->startOfWeek())
            ->where('dt_complete_by', '<=', now()->endOfWeek())
            ->open()
            ->get()
            ->groupBy('user_id');

        $overdueTasks = Task::query()->with('user')->orderBy('dt_complete_by')
            ->where('dt_complete_by', '<', now()->startOfDay())
            ->open()
            ->get()
            ->groupBy('user_id');

        $userIds = $appointments?->keys()
            ->merge($tasks?->keys())
            ->merge($overdueTasks?->keys())
            ->unique();


        foreach ($userIds as $userId) {
            $user = User::query()->find($userId);
            $userAppointments = $appointments->get($userId, collect());
            $userTasks = $tasks->get($userId, collect());
            $userOverdueTasks = $overdueTasks->get($userId, collect());

            if($user){
                Mail::to($user->email)->send(new WeeklyAppointmentReminderMail($user, $userAppointments, $userTasks, $userOverdueTasks));
            }
        }
    }
}
