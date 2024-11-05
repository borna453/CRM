<?php

namespace App\Enums;

use App\Mail\WeeklyAppointmentReminderMail;
use App\Notifications\AppointmentCreated;
use App\Notifications\AppointmentUpdatedNotification;
use App\Notifications\CommentCreated;
use App\Notifications\CompanyCreationReminder;
use App\Notifications\PinboardItemNotification;
use App\Notifications\AppointmentDeletedNotification;
use App\Notifications\ReportPublished;
use App\Notifications\TaskAssigned;
use App\Notifications\TasksReminder;
use App\Notifications\TenantWelcomeEmailNotification;
use App\Notifications\UserWelcomeEmail;

enum NotificationTypeEnum: string
{
    case APPOINTMENT_CREATED = 'appointment_created';
    case APPOINTMENT_UPDATED = 'appointment_updated';
    case APPOINTMENT_CANCELED = 'appointment_canceled';
    case COMMENT_CREATED = 'comment_created';
    case COMPANY_CREATION_REMINDER = 'company_creation_reminder';
    case PINBOARD_ITEM_CREATED = 'pinboard_item_created';
    case REPORT_PUBLISHED = 'report_published';
    case TASK_ASSIGNED = 'task_assigned';
    case TASKS_REMINDER = 'tasks_reminder';
    case USER_WELCOME_EMAIL = 'user_welcome_email';
    case TENANT_WELCOME_EMAIL = 'tenant_welcome_email';
    case WEEKLY_REMINDER_EMAIL = 'weekly_reminder_email';

    public function notificationClass(): string
    {
        return match($this){
            self::APPOINTMENT_CREATED => AppointmentCreated::class,
            self::APPOINTMENT_UPDATED => AppointmentUpdatedNotification::class,
            self::APPOINTMENT_CANCELED => AppointmentDeletedNotification::class,
            self::COMMENT_CREATED => CommentCreated::class,
            self::COMPANY_CREATION_REMINDER => CompanyCreationReminder::class,
            self::PINBOARD_ITEM_CREATED => PinboardItemNotification::class,
            self::REPORT_PUBLISHED => ReportPublished::class,
            self::TASK_ASSIGNED => TaskAssigned::class,
            self::TASKS_REMINDER => TasksReminder::class,
            self::USER_WELCOME_EMAIL => UserWelcomeEmail::class,
            self::TENANT_WELCOME_EMAIL => TenantWelcomeEmailNotification::class,
            self::WEEKLY_REMINDER_EMAIL => WeeklyAppointmentReminderMail::class,
        };
    }

    public function label(): string
    {
        return match($this){
            self::APPOINTMENT_CREATED => __('portal.notifications.appointment.label_created'),
            self::APPOINTMENT_UPDATED => __('portal.notifications.appointment.label_updated'),
            self::APPOINTMENT_CANCELED => __('portal.notifications.appointment.label_canceled'),
            self::COMMENT_CREATED => __('portal.notifications.comment.label'),
            self::COMPANY_CREATION_REMINDER => __('portal.notifications.company.label'),
            self::PINBOARD_ITEM_CREATED => __('portal.notifications.pinboard_item.label'),
            self::REPORT_PUBLISHED => __('portal.notifications.report.label'),
            self::TASK_ASSIGNED => __('portal.notifications.task.label_assigned'),
            self::TASKS_REMINDER => __('portal.notifications.task.label_reminder'),
            self::USER_WELCOME_EMAIL => __('portal.notifications.user.label'),
            self::TENANT_WELCOME_EMAIL => __('portal.notifications.tenant.label'),
            self::WEEKLY_REMINDER_EMAIL => __('portal.notifications.weekly_reminder_email.label'),
        };
    }

    public function description(): string
    {
        return match($this){
            self::APPOINTMENT_CREATED => __('portal.notifications.appointment.description_created'),
            self::APPOINTMENT_UPDATED => __('portal.notifications.appointment.description_updated'),
            self::APPOINTMENT_CANCELED => __('portal.notifications.appointment.description_canceled'),
            self::COMMENT_CREATED => __('portal.notifications.comment.description'),
            self::COMPANY_CREATION_REMINDER => __('portal.notifications.company.description'),
            self::PINBOARD_ITEM_CREATED => __('portal.notifications.pinboard_item.description'),
            self::REPORT_PUBLISHED => __('portal.notifications.report.description'),
            self::TASK_ASSIGNED => __('portal.notifications.task.description_assigned'),
            self::TASKS_REMINDER => __('portal.notifications.task.description_reminder'),
            self::USER_WELCOME_EMAIL => __('portal.notifications.user.description'),
            self::TENANT_WELCOME_EMAIL => __('portal.notifications.tenant.description'),
            self::WEEKLY_REMINDER_EMAIL => __('portal.notifications.weekly_reminder_email.description'),
        };
    }
}
