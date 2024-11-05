<?php

namespace App\Utils\Notifications;

use App\Enums\NotificationTypeEnum;
use App\Mail\WeeklyAppointmentReminderMail;
use App\Notifications\TasksReminder;
use Illuminate\Notifications\Notification;

class NotificationHelper
{
    public static function getNotificationChannels($notifiable)
    {
        if ($notifiable->email_enabled) {
            return ['database', 'mail'];
        }
        return ['database'];
    }
    public static function replaceVariables($content, $params)
    {
        foreach ($params as $key => $value) {
            if (is_array($value)) {
                continue;
            }
            $content = str_replace("{{$key}}", $value, $content);
        }
        return $content;
    }

    public static function replaceTasksTable($content, $taskRows)
    {
        $tasksTableHtml = '<table style="width: 100%; border-collapse: collapse; margin-top: 20px;">';

        $tasksTableHtml .= '<thead>
        <tr>
            <th style="padding: 10px; border-bottom: 2px solid #ddd; text-align: left; background-color: #f9f9f9; color: #333;">Onderwerp</th>
            <th style="padding: 10px; border-bottom: 2px solid #ddd; text-align: left; background-color: #f9f9f9; color: #333;">Deadline</th>
        </tr>
    </thead>
    <tbody>';

        foreach ($taskRows as $taskRow) {
            $tasksTableHtml .= "<tr>
        <td style=\"padding: 10px; border-bottom: 1px solid #ddd;\">{$taskRow['title']}</td>
        <td style=\"padding: 10px; border-bottom: 1px solid #ddd;\">{$taskRow['deadline']}</td>
    </tr>";
        }
        $tasksTableHtml .= '</tbody></table>';

        $replace = str_replace("{tasks_table}", $tasksTableHtml, $content);

        if(empty($taskRows)){
            $replace = str_replace("{tasks_table}",null, $content);
        }

        return $replace;
    }

    public static function replaceAppointmentsTable($content, $appointmentRows)
    {
        $appointmentsTableHtml =
            '<table style="width: 100%; border-collapse: collapse; margin-top: 20px;">';

        $appointmentsTableHtml .=
            '<thead>
                <tr>
                    <th style="padding: 10px; border-bottom: 2px solid #ddd; text-align: left; background-color: #f9f9f9; color: #333;">Afspraak moment</th>
                    <th style="padding: 10px; border-bottom: 2px solid #ddd; text-align: left; background-color: #f9f9f9; color: #333;">Titel</th>
                </tr>
            </thead>
            <tbody>';

        foreach ($appointmentRows as $appointmentRow) {
            $appointmentsTableHtml .=
                "<tr>
                    <td style=\"padding: 10px; border-bottom: 1px solid #ddd;\">{$appointmentRow['appointment_start']} - {$appointmentRow['appointment_end']}</td>
                    <td style=\"padding: 10px; border-bottom: 1px solid #ddd;\">{$appointmentRow['appointment_title']}</td>
                </tr>";
        }
        $appointmentsTableHtml .= '</tbody></table>';

        $replace = str_replace("{appointments_table}", $appointmentsTableHtml, $content);

        if(empty($appointmentRows)){
            $replace = str_replace("{appointments_table}",null, $content);
        }

        return $replace;
    }

    public static function processNotificationContent($typeValue, $emailContent, $buttonText = null)
    {
        if (!$typeValue instanceof NotificationTypeEnum) {
            $typeValue = NotificationTypeEnum::from($typeValue);
        }

        $notificationClass = $typeValue->notificationClass();

        /** @var Notification $fakeNotification */
        $fakeNotification = $notificationClass::fake();

        $params = $fakeNotification->templateParams(buttonText: $buttonText, preview: true);

        $emailContent = self::replaceVariables($emailContent, $params);

        if ($notificationClass === TasksReminder::class) {
            $emailContent = self::replaceTasksTable($emailContent, $params['tasks_table']);
        }

        if ($notificationClass === WeeklyAppointmentReminderMail::class) {
            $emailContent = htmlspecialchars_decode($emailContent);

            $emailContent = self::replaceTasksTable($emailContent, $params['tasks_table']);

            $emailContent = self::replaceAppointmentsTable($emailContent, $params['appointments_table']);
        }

        return [$typeValue, $emailContent, $params];
    }

    public static function generateTenantUrl($tenantId, $route, $routeParams): string
    {
        $url = route($route, $routeParams);

        $parsedUrl = parse_url($url);

        $scheme = $parsedUrl['scheme'] ?? 'http';
        $host = "{$tenantId}." . config('custom.central_domain');
        $path = $parsedUrl['path'] ?? '';
        $query = isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : '';

        return "{$scheme}://{$host}{$path}{$query}";
    }
}
