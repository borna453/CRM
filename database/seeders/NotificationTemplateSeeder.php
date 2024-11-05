<?php

namespace Database\Seeders;

use App\Enums\NotificationTypeEnum;
use App\Models\NotificationTemplate;
use Illuminate\Database\Seeder;

class NotificationTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'type' => NotificationTypeEnum::APPOINTMENT_CREATED,
                'email_subject' => __('portal.notifications.appointment.subject', [], 'nl'),
                'email_content' => <<<HTML
                    <h1>Hallo!</h1>
                    <p>De afspraak is gepland op {datum} om {tijd} uur. Je kunt de afspraak toevoegen aan je eigen agenda via de bijlage.</p>
                    <p>{button}</p>
                    <p>Tot dan!<br>{organizer_naam}</p>
                    HTML,
                'button_text' => __('portal.notifications.appointment.view'),
            ],
            [
                'type' => NotificationTypeEnum::APPOINTMENT_UPDATED,
                'email_subject' => __('portal.notifications.appointment.subject_updated', [], 'nl'),
                'email_content' => <<<HTML
                    <h1>Hallo!</h1>
                    <p>De afspraak is bijgewerkt voor {datum} om {tijd}. Je kunt de afspraak bijwerken in je eigen agenda via de bijlage.</p>
                    <p>{button}</p>
                    <p>Tot dan!<br>{organizer_naam}</p>
                    HTML,
                'button_text' => __('portal.notifications.appointment.view'),
            ],
            [
                'type' => NotificationTypeEnum::APPOINTMENT_CANCELED,
                'email_subject' => __('portal.notifications.appointment.deleted', [], 'nl'),
                'email_content' => <<<HTML
                    <h1>Hallo!</h1>
                    <p>De afspraak is geannuleerd {titel}</p>
                    <p>Tot ziens!<br>{organizer_naam}</p>
                    HTML,
            ],
            [
                'type' => NotificationTypeEnum::COMMENT_CREATED,
                'email_subject' => __('portal.notifications.comment.subject', [], 'nl'),
                'email_content' => <<<HTML
                    <h1>Hallo!</h1>
                    <p>Er is een nieuwe opmerking toegevoegd aan verslag : {report_titel}</p>
                    <p>Opmerking: {comment_text}</p>
                    <p>Met vriendelijke groet,<br>{comment_creator}</p>
                    HTML,
                'button_text' => __('portal.notifications.comment.view'),
            ],
            [
                'type' => NotificationTypeEnum::COMPANY_CREATION_REMINDER,
                'email_subject' => __('portal.notifications.company.creation_reminder', [], 'nl'),
                'email_content' => <<<HTML
                    <h1>Hallo!</h1>
                    <p>Voltooi het aanmaken van het bedrijf {company_name}</p>
                    <p>{button}</p>
                    <p>Met vriendelijke groet,<br>{tenant_email_from}</p>
                    HTML,
                'button_text' => __('portal.notifications.company.view_company'),
            ],
            [
                'type' => NotificationTypeEnum::PINBOARD_ITEM_CREATED,
                'email_subject' => __('portal.notifications.pinboard_item.subject_created', [], 'nl'),
                'email_content' => <<<HTML
                    <h1>Hallo!</h1>
                    <p>Er is een nieuw prikborditem aangemaakt: {omschrijving}</p>
                    <p>{button}</p>
                    <p>Met vriendelijke groet,<br>{creator}</p>
                    HTML,
                'button_text' => __('portal.notifications.pinboard_item.view'),
            ],
            [
                'type' => NotificationTypeEnum::REPORT_PUBLISHED,
                'email_subject' => __('portal.notifications.report.subject', [], 'nl'),
                'email_content' => <<<HTML
                    <h1>Hallo!</h1>
                    <p>Er is een nieuw rapport gepubliceerd: {titel}</p>
                    <p>{button}</p>
                    <p>Met vriendelijke groet,<br>{tenant_email_from}</p>
                    HTML,
                'button_text' => __('portal.notifications.report.view'),
            ],
            [
                'type' => NotificationTypeEnum::TASK_ASSIGNED,
                'email_subject' => __('portal.notifications.task.subject_assigned', [], 'nl'),
                'email_content' => <<<HTML
                    <h1>Hallo!</h1>
                    <p>Je hebt een nieuwe taak toegewezen gekregen: {title}</p>
                    <p>{button}</p>
                    <p>Met vriendelijke groet,<br>{creator}</p>
                    HTML,
                'button_text' => __('portal.notifications.task.view'),
            ],
            [
                'type' => NotificationTypeEnum::TASKS_REMINDER,
                'email_subject' => __('portal.notifications.task.subject_reminder', [], 'nl'),
                'email_content' => <<<HTML
                    <h1>Hallo!</h1>
                    <p>U heeft meerdere taken die moeten worden uitgevoerd</p>
                    <!-- START TASKS TABLE -->
                    {tasks_table}
                    <!-- END TASKS TABLE -->
                    {button}
                    <p>Met vriendelijke groet,<br>{tenant_from_name}</p>
                    HTML,
                'button_text' => __('portal.notifications.task.view'),
            ],
            [
                'type' => NotificationTypeEnum::USER_WELCOME_EMAIL,
                'email_subject' => __('portal.notifications.user.subject', [], 'nl'),
                'email_content' => <<<HTML
                    <h1>Hallo!</h1>
                    <p>Welkom op ons platform. We zijn blij dat je er bent.</p>
                    <p><strong>E-mail:</strong> {user_email}</p>
                    <p>{button}</p>
                    <p>Met vriendelijke groet,<br>{tenant_email_from}</p>
                    HTML,

            ],
            [
                'type' => NotificationTypeEnum::TENANT_WELCOME_EMAIL,
                'email_subject' => __('portal.notifications.tenant.subject', [], 'nl'),
                'email_content' => <<<HTML
                    <h1>Hallo, {user_name_full}</h1>
                    <p>Welkom op ons platform. We zijn blij dat je er bent.</p>
                    <p>{button}</p>
                    <p>Met vriendelijke groet,<br>{tenant_email_from}</p>
                    HTML,
            ],
            [
                'type' => NotificationTypeEnum::WEEKLY_REMINDER_EMAIL,
                'email_subject' => __('email.weekly_reminder_email.subject', [], 'nl'),
                'email_content' => <<<HTML
                <h1>Geachte, {user_name_full},</h1>

                <p>Dit zijn uw taken en afspraken voor deze week: <br></p>
                    <!-- START TASKS TABLE -->
                    <br>{tasks_table}<br>
                    <!-- END TASKS TABLE -->
                    <!-- START APPOINTMENTS TABLE -->
                    {appointments_table}<br>
                    <!-- END APPOINTMENTS TABLE -->
                    <p>Met vriendelijke groet,<br>{tenant_from_name}</p>
                HTML,
            ]
        ];

        foreach ($templates as $template){
            NotificationTemplate::updateOrCreate($template)->withoutTenancy();
        }
    }
}
