<?php

namespace App\Http\Controllers;

use App\Enums\NotificationTypeEnum;
use App\Mail\WeeklyAppointmentReminderMail;
use App\Notifications\Contracts\NotificationTemplateDefinition;
use App\Notifications\TasksReminder;
use App\Utils\Notifications\NotificationHelper;
use Illuminate\Http\Request;
use Illuminate\Mail\Markdown;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;
use UnexpectedValueException;

class PreviewNotificationController extends Controller
{
    public function __construct(
        protected Markdown $markdown
    ) {
    }

    public function __invoke(Request $request): HtmlString
    {
        $request->validate([
            'type' => 'required|string',
            'email_content' => 'nullable|string',
            'params' => 'nullable|array',
        ]);

        $typeValue = $request->input('type');

        try {
            $type = NotificationTypeEnum::from($typeValue);
        } catch (UnexpectedValueException $e) {
            abort(404);
        }

        $emailContent = $request->input('email_content');
        $buttonText = $request->input('button_text');
        $givenParams = $request->input('params', []);

        [$type, $emailContent, $params] = NotificationHelper::processNotificationContent($type, $emailContent, $buttonText);


        if (empty($givenParams)) {
            $givenParams = [];
        }
        $dynamicView = 'emails.dynamic_notification';

        return $this->markdown->render($dynamicView, array_merge($givenParams, [
            'type' => $type->value,
            'email_content' => $emailContent,
            'preview' => true,
        ]));
    }
}
