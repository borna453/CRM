<?php

namespace App\Enums;

enum OnboardingTypes: string
{
    case ADD_USER = 'add_user';
//    case ADD_EMPLOYEE = 'add_employee';
//    case ASSIGN_TASK = 'assign_task';
    case ADD_FINANCIAL_GOAL = 'add_financial_goal';
    case EDIT_NOTIFICATION_TEMPLATE = 'edit_notification_template';
//    case ADD_OPPORTUNITY = 'add_opportunity';
//    case IMPERSONATE_USER = 'impersonate_user';
//    case ADD_APPOINTMENT = 'add_appointment';
//    case ADD_REPORT = 'add_report';
//    case EDIT_EMAIL_SETTINGS = 'edit_email_settings';

    public static function getOrderedSteps(): array
    {
        return [
            self::ADD_USER,
            self::ADD_FINANCIAL_GOAL,
            self::EDIT_NOTIFICATION_TEMPLATE,
//            self::ADD_OPPORTUNITY,
//            self::ADD_EMPLOYEE,
//            self::ASSIGN_TASK,
//            self::IMPERSONATE_USER,
//            self::ADD_APPOINTMENT,
//            self::ADD_REPORT,
//            self::EDIT_EMAIL_SETTINGS,
        ];
    }
}
