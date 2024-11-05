<?php

namespace App\Mail;

use App\Enums\NotificationTypeEnum;
use App\Models\Appointment;
use App\Models\NotificationTemplate;
use App\Models\Task;
use App\Models\User;
use App\Traits\GetEmailContentTrait;
use App\Traits\SafelyFakeNotificationTrait;
use App\Utils\Notifications\NotificationHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WeeklyAppointmentReminderMail extends Mailable
{
    use Queueable;
    use SerializesModels;
    use SafelyFakeNotificationTrait;
    use GetEmailContentTrait;

    protected $user;
    protected $appointments;
    protected $tasks;

    private $emailContent;
    private NotificationTemplate $emailTemplate;

    public function __construct(User $user, $appointments, $tasks, $overdueTasks)
    {
        $this->user = $user;

        $this->appointments = $appointments;

        $this->tasks = $overdueTasks->merge($tasks);

        $this->emailTemplate = $this->getEmailModel(NotificationTypeEnum::WEEKLY_REMINDER_EMAIL);

        $this->emailContent = $this->emailTemplate->email_content;
    }

    public function build()
    {
        $params = $this->templateParams();

        $emailContent = NotificationHelper::replaceVariables($this->emailContent, $params);

        $emailContent = NotificationHelper::replaceTasksTable($emailContent, $params['tasks_table']);

        $emailContent = NotificationHelper::replaceAppointmentsTable($emailContent, $params['appointments_table']);

        return $this
            ->markdown('emails.dynamic_notification',
                ['email_content' => $emailContent]
            )
            ->subject($this->emailTemplate->email_subject);
    }


    public function templateParams($buttonText = null, $preview = false)
    {
        if($this->tasks){
            $taskRows = $this->tasks->map(function (Task $task) {
                return [
                    'title' => $task->title ?? '',
                    'deadline' => $task->dt_complete_by?->format('d-m-Y') ?? '',
                ];
            })->toArray();
        }

        if($this->appointments){
            $appointmentRows = $this->appointments->map(function (Appointment $appointment) {
                return [
                    'appointment_title' => $appointment->title ?? '',
                    'appointment_start' => $appointment->dt_start?->format('d-m-Y H:i') ?? '',
                    'appointment_end' => $appointment->dt_end?->format('H:i') ?? '',
                ];
            })->toArray();
        }

         return [
            'user_name' => $this->user->name,
            'user_last_name' => $this->user->last_name,
            'user_name_full' => $this->user->name,
            'tenant_from_name' => tenant()?->email['from_name'] ?? config('app.name'),
            'tasks_table' => $taskRows ?? [],
            'appointments_table' => $appointmentRows ?? [],
        ];
    }

    public static function templateParamsList(): array
    {
        return [
            'user_name' => __('portal.notifications.params.user_name'),
            'user_last_name' => __('portal.notifications.params.user_last_name'),
            'user_name_full' => __('portal.notifications.params.user_name_full'),
            'tenant_from_name' => __('portal.notifications.params.tenant_email_from'),
            'tasks_table' => __('portal.notifications.task.parameters.tasks_table'),
            'appointments_table' => __('portal.notifications.appointment.parameters.appointments_table'),
        ];
    }

    public static function fake(): Mailable
    {
        $notification = null;

        self::safelyFake(function () use (&$notification) {
            $notification = new self(
                User::factory()::preview(),
                collect(Appointment::factory()->count(3)->make([
                    'title' => 'Zakelijke Bespreking',
                    'location' => 'my_location',
                    'online_url' => 'https://voorbeeld.com/online-vergadering',
                    'description' => 'Dit is een zakelijke bespreking over het nieuwe project en de aanstaande deadlines.',
                    'dt_start' => now(),
                    'dt_end' => now()->addHour(),
                ])),
                collect(Task::factory()->count(3)->make([
                    'title' => 'Test titel',
                    'dt_complete_by' => now(),
                    'information' => 'Test informatie',
                ])),
                collect(Task::factory()->count(3)->make([
                    'title' => 'Test overdue task titel',
                    'dt_complete_by' => now()->subDay(),
                    'information' => 'Test informatie',
                ]))
            );
        });

        return $notification;
    }
}
