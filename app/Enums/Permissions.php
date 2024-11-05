<?php

namespace App\Enums;

enum Permissions: string
{
    // Admin Resources
    case VIEW_APPOINTMENTS = 'appointments.view';
    case EDIT_APPOINTMENTS = 'appointments.edit';
    case CREATE_APPOINTMENTS = 'appointments.create';
    case DELETE_APPOINTMENTS = 'appointments.delete';
    case RESTORE_APPOINTMENTS = 'appointments.restore';

    case VIEW_COMPANIES = 'companies.view';
    case EDIT_COMPANIES = 'companies.edit';
    case CREATE_COMPANIES = 'companies.create';
    case DELETE_COMPANIES = 'companies.delete';
    case RESTORE_COMPANIES = 'companies.restore';

    case VIEW_CONTRACTS = 'contracts.view';
    case EDIT_CONTRACTS = 'contracts.edit';
    case CREATE_CONTRACTS = 'contracts.create';
    case DELETE_CONTRACTS = 'contracts.delete';

    case VIEW_DEALS = 'deals.view';
    case EDIT_DEALS = 'deals.edit';
    case CREATE_DEALS = 'deals.create';
    case DELETE_DEALS = 'deals.delete';

    case VIEW_CALL_EVENTS = 'call_events.view';

    case VIEW_FEATURE_SETTINGS = 'feature_settings.view';
    case EDIT_FEATURE_SETTINGS = 'feature_settings.edit';

    case VIEW_LABELS = 'labels.view';
    case EDIT_LABELS = 'labels.edit';
    case CREATE_LABELS = 'labels.create';
    case DELETE_LABELS = 'labels.delete';
    case REORDER_LABELS = 'labels.reorder';

    case VIEW_MESSAGES = 'messages.view';
    case CREATE_MESSAGES = 'messages.create';

    case VIEW_NOTIFICATION_TEMPLATES = 'notification_templates.view';
    case EDIT_NOTIFICATION_TEMPLATES = 'notification_templates.edit';

    case VIEW_OPPORTUNITIES = 'opportunities.view';
    case EDIT_OPPORTUNITIES = 'opportunities.edit';
    case CREATE_OPPORTUNITIES = 'opportunities.create';
    case DELETE_OPPORTUNITIES = 'opportunities.delete';

    case VIEW_PINBOARD_ITEMS = 'pinboard_items.view';
    case CREATE_PINBOARD_ITEMS = 'pinboard_items.create';
    case EDIT_PINBOARD_ITEMS = 'pinboard_items.edit';
    case DELETE_PINBOARD_ITEMS = 'pinboard_items.delete';

    case VIEW_REPORTS = 'reports.view';
    case EDIT_REPORTS = 'reports.edit';
    case CREATE_REPORTS = 'reports.create';
    case DELETE_REPORTS = 'reports.delete';
    case RESTORE_REPORTS = 'reports.restore';
    case ADD_COMMENTS_TO_REPORTS = 'reports.add_comments';

    case VIEW_TASKS = 'tasks.view';
    case EDIT_TASKS = 'tasks.edit';
    case CREATE_TASKS = 'tasks.create';
    case DELETE_TASKS = 'tasks.delete';
    case RESTORE_TASKS = 'tasks.restore';

    case VIEW_UNBILLED_APPOINTMENTS = 'unbilled_appointments.view';
    case EDIT_UNBILLED_APPOINTMENTS = 'unbilled_appointments.edit';

    case VIEW_USERS = 'users.view';
    case EDIT_USERS = 'users.edit';
    case CREATE_USERS = 'users.create';
    case DELETE_USERS = 'users.delete';
    case RESTORE_USERS = 'users.restore';
    case IMPERSONATE_USERS = 'users.impersonate';

    case VIEW_NOTES = 'notes.view';
    case CREATE_NOTES = 'notes.create';
    case EDIT_NOTES = 'notes.edit';
    case DELETE_NOTES = 'notes.delete';

    // Admin Pages
    case VIEW_EMAIL_LOGS = 'email_logs.view';
    case VIEW_CHOICE_DEAL_CONTRACT = 'choice_deal_contract.view';
    case CREATE_CHOICE_DEAL_CONTRACT = 'choice_deal_contract.create';

    case VIEW_MANAGE_EMAIL_SETTINGS = 'manage_email_settings.view';
    case EDIT_MANAGE_EMAIL_SETTINGS = 'manage_email_settings.edit';

    case VIEW_MANAGE_GENERAL_SETTINGS = 'manage_general_settings.view';
    case EDIT_MANAGE_GENERAL_SETTINGS = 'manage_general_settings.edit';

    case VIEW_ONLINE_USERS = 'online_users.view';

    case VIEW_OPPORTUNITIES_KANBAN_BOARD = 'opportunities_kanban_board.view';
    case EDIT_OPPORTUNITIES_KANBAN_BOARD = 'opportunities_kanban_board.edit';
    case CREATE_OPPORTUNITIES_KANBAN_BOARD = 'opportunities_kanban_board.create';
    case DELETE_OPPORTUNITIES_KANBAN_BOARD = 'opportunities_kanban_board.delete';

    // Admin Widgets
    case VIEW_UPCOMING_APPOINTMENTS_CALENDAR = 'upcoming_appointments_calendar.view';
    case VIEW_COMPANY_OVERVIEW_WIDGET = 'company_overview_widget.view';
    case VIEW_ENGAGEMENT_OVERVIEW_WIDGET = 'engagement_overview_widget.view';
    case VIEW_OPPORTUNITY_WIDGET = 'opportunity_widget.view';
    case VIEW_OPEN_TASK_WIDGET = 'open_task_widget.view';
    case EDIT_OPEN_TASK_WIDGET = 'open_task_widget.edit';

    // User Resources
    case VIEW_USER_APPOINTMENTS = 'user_appointments.view';
    case VIEW_USER_MESSAGE_RESOURCES = 'user_message_resources.view';
    case VIEW_USER_PINBOARD_ITEMS = 'user_pinboard_items.view';
    case EDIT_USER_PINBOARD_ITEMS = 'user_pinboard_items.edit';
    case DELETE_USER_PINBOARD_ITEMS = 'user_pinboard_items.delete';
    case CREATE_USER_PINBOARD_ITEMS = 'user_pinboard_items.create';

    case VIEW_USER_REPORTS = 'user_reports.view';
    case USER_ADD_COMMENTS_TO_REPORTS = 'user_reports.add_comments';

    case VIEW_USER_TASKS = 'user_tasks.view';
    case EDIT_USER_TASKS = 'user_tasks.edit';
    case CREATE_USER_TASKS = 'user_tasks.create';
    case DELETE_USER_TASKS = 'user_tasks.delete';

    // User Pages
    case VIEW_USER_APPOINTMENT_HISTORY = 'appointment_history.view';

    // User Widgets
    case VIEW_USER_UPCOMING_APPOINTMENTS_CALENDAR = 'user_upcoming_appointments_calendar.view';
    case VIEW_USER_OPEN_PINBOARD_ITEMS = 'user_open_pinboard_items.view';
    case EDIT_USER_OPEN_PINBOARD_ITEMS = 'user_open_pinboard_items.edit';
    case VIEW_USER_OPEN_TASK_WIDGET = 'user_open_task_widget.view';
    case EDIT_USER_OPEN_TASK_WIDGET = 'user_open_task_widget.edit';

    public static function groups(): array
    {
        return [
            'Admin Resources' => [
                'Appointments' => [
                    self::VIEW_APPOINTMENTS,
                    self::EDIT_APPOINTMENTS,
                    self::CREATE_APPOINTMENTS,
                    self::DELETE_APPOINTMENTS,
                    self::RESTORE_APPOINTMENTS,
                ],
                'Companies' => [
                    self::VIEW_COMPANIES,
                    self::EDIT_COMPANIES,
                    self::CREATE_COMPANIES,
                    self::DELETE_COMPANIES,
                    self::RESTORE_COMPANIES,
                ],
                'Contracts' => [
                    self::VIEW_CONTRACTS,
                    self::EDIT_CONTRACTS,
                    self::CREATE_CONTRACTS,
                    self::DELETE_CONTRACTS,
                ],
                'Deals' => [
                    self::VIEW_DEALS,
                    self::EDIT_DEALS,
                    self::CREATE_DEALS,
                    self::DELETE_DEALS,
                ],
                'Call Events' => [
                    self::VIEW_CALL_EVENTS,
                ],
                'Feature Settings' => [
                    self::VIEW_FEATURE_SETTINGS,
                    self::EDIT_FEATURE_SETTINGS,
                ],
                'Labels' => [
                    self::VIEW_LABELS,
                    self::EDIT_LABELS,
                    self::CREATE_LABELS,
                    self::DELETE_LABELS,
                    self::REORDER_LABELS,
                ],
                'Messages' => [
                    self::VIEW_MESSAGES,
                    self::CREATE_MESSAGES,
                ],
                'Notification Templates' => [
                    self::VIEW_NOTIFICATION_TEMPLATES,
                    self::EDIT_NOTIFICATION_TEMPLATES,
                ],
                'Opportunities' => [
                    self::VIEW_OPPORTUNITIES,
                    self::EDIT_OPPORTUNITIES,
                    self::CREATE_OPPORTUNITIES,
                    self::DELETE_OPPORTUNITIES,
                ],
                'Reports' => [
                    self::VIEW_REPORTS,
                    self::EDIT_REPORTS,
                    self::CREATE_REPORTS,
                    self::DELETE_REPORTS,
                    self::RESTORE_REPORTS,
                    self::ADD_COMMENTS_TO_REPORTS,
                ],
                'Tasks' => [
                    self::VIEW_TASKS,
                    self::EDIT_TASKS,
                    self::CREATE_TASKS,
                    self::DELETE_TASKS,
                    self::RESTORE_TASKS,
                ],
                'Unbilled Appointments' => [
                    self::VIEW_UNBILLED_APPOINTMENTS,
                    self::EDIT_UNBILLED_APPOINTMENTS,
                ],
                'Users' => [
                    self::VIEW_USERS,
                    self::EDIT_USERS,
                    self::CREATE_USERS,
                    self::DELETE_USERS,
                    self::RESTORE_USERS,
                    self::IMPERSONATE_USERS,
                ],
                'Pinboard Items' => [
                    self::VIEW_PINBOARD_ITEMS,
                    self::CREATE_PINBOARD_ITEMS,
                ],
                'Notes' => [
                    self::VIEW_NOTES,
                    self::CREATE_NOTES,
                    self::EDIT_NOTES,
                    self::DELETE_NOTES,
                ],
            ],
            'Admin Pages' => [
                'Email Logs' => [
                    self::VIEW_EMAIL_LOGS,
                ],
                'Choice Deal Contract' => [
                    self::VIEW_CHOICE_DEAL_CONTRACT,
                    self::CREATE_CHOICE_DEAL_CONTRACT,
                ],
                'Manage Email Settings' => [
                    self::VIEW_MANAGE_EMAIL_SETTINGS,
                    self::EDIT_MANAGE_EMAIL_SETTINGS,
                ],
                'Manage General Settings' => [
                    self::VIEW_MANAGE_GENERAL_SETTINGS,
                    self::EDIT_MANAGE_GENERAL_SETTINGS,
                ],
                'Online Users' => [
                    self::VIEW_ONLINE_USERS,
                ],
                'Opportunities Kanban Board' => [
                    self::VIEW_OPPORTUNITIES_KANBAN_BOARD,
                    self::EDIT_OPPORTUNITIES_KANBAN_BOARD,
                    self::CREATE_OPPORTUNITIES_KANBAN_BOARD,
                    self::DELETE_OPPORTUNITIES_KANBAN_BOARD,
                ],
            ],
            'Admin Widgets' => [
                'Upcoming Appointments Calendar' => [
                    self::VIEW_UPCOMING_APPOINTMENTS_CALENDAR,
                ],
                'Company Overview' => [
                    self::VIEW_COMPANY_OVERVIEW_WIDGET,
                ],
                'Engagement Overview' => [
                    self::VIEW_ENGAGEMENT_OVERVIEW_WIDGET,
                ],
                'Opportunity Widget' => [
                    self::VIEW_OPPORTUNITY_WIDGET,
                ],
                'Open Task Widget' => [
                    self::VIEW_OPEN_TASK_WIDGET,
                    self::EDIT_OPEN_TASK_WIDGET,
                ],
            ],
            'User Resources' => [
                'User Appointments' => [
                    self::VIEW_USER_APPOINTMENTS,
                ],
                'Messages' => [
                    self::VIEW_USER_MESSAGE_RESOURCES,
                ],
                'Pinboard Items' => [
                    self::VIEW_USER_PINBOARD_ITEMS,
                    self::EDIT_USER_PINBOARD_ITEMS,
                    self::CREATE_USER_PINBOARD_ITEMS,
                    self::DELETE_USER_PINBOARD_ITEMS,
                ],
                'Reports' => [
                    self::VIEW_USER_REPORTS,
                    self::USER_ADD_COMMENTS_TO_REPORTS,
                ],
                'Tasks' => [
                    self::VIEW_USER_TASKS,
                    self::EDIT_USER_TASKS,
                    self::CREATE_USER_TASKS,
                    self::DELETE_USER_TASKS,
                ],
            ],
            'User Pages' => [
                'Appointment History' => [
                    self::VIEW_USER_APPOINTMENT_HISTORY,
                ],
            ],
            'User Widgets' => [
                'Upcoming Appointments Calendar' => [
                    self::VIEW_USER_UPCOMING_APPOINTMENTS_CALENDAR,
                ],
                'Open Pinboard Items' => [
                    self::VIEW_USER_OPEN_PINBOARD_ITEMS,
                    self::EDIT_USER_OPEN_PINBOARD_ITEMS,
                ],
                'Open Task Widget' => [
                    self::VIEW_USER_OPEN_TASK_WIDGET,
                    self::EDIT_USER_OPEN_TASK_WIDGET,
                ],
            ],
        ];
    }
}
