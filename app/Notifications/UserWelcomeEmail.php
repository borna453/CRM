<?php

namespace App\Notifications;

use App\Enums\NotificationTypeEnum;
use App\Models\Tenant;
use App\Models\User;
use App\Utils\Notifications\NotificationButtonHelper;
use Filament\Facades\Filament;
use Illuminate\Notifications\Notification;

class UserWelcomeEmail extends BaseNotification
{
    public function __construct(private readonly User $user)
    {
        $this->message = __('portal.notifications.user.welcome');
        $this->user->load('tenant');
        parent::__construct(['user' => $this->user]);
    }

    protected function getType(): string
    {
        return NotificationTypeEnum::USER_WELCOME_EMAIL->value;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function templateParams($buttonText = null, $preview = false): array
    {
        $buttonLabel = $buttonText ?? __('portal.notifications.user.reset_password');

        return [
            'user_name_full' => $this->user->name ?? '',
            'user_name' => $this->user->first_name ?? '',
            'user_last_name' => $this->user->last_name ?? '',
            'user_email' => $this->user->email ?? '',
            ...$this->getTenantEmailInformation($this->user),
            'button' => NotificationButtonHelper::generateButtonHtml($buttonLabel, $this->resetUrl($this->user, $preview)),
        ];
    }

    public static function templateParamsList(): array
    {
        return [
            'user_name' => __('portal.notifications.params.user_name'),
            'user_name_full' => __('portal.notifications.params.user_name_full'),
            'user_last_name' => __('portal.notifications.params.user_last_name'),
            'user_email' => __('portal.notifications.params.user_email'),
            ...self::getTenantEmailTemplateInformation(),
            'button' => __('portal.notifications.params.button'),
        ];
    }

    public static function fake(): Notification
    {
        $notification = null;

        self::safelyFake(function () use (&$notification) {
            $notification = new self(
                User::factory()->preview([
                    'name' => 'Test User',
                    'email' => 'test@email.nl'
                ])
                    ->setRelation('tenant', Tenant::factory()->preview([
                        'id' => auth()->user()->tenant_id ?? 'Cloudmazing'
                    ]))
            );
        });

        return $notification;
    }

    protected function resetUrl(mixed $notifiable, $preview = false): string
    {
        if(!$preview){
            $tenant = $this->user->tenant;

            request()->headers->set('HOST', $tenant->getHost());

            $token = app('auth.password.broker')->createToken($notifiable);

            $baseResetUrl = Filament::getPanel('admin')->getResetPasswordUrl($token, $notifiable);
        }

        if($preview){
            $baseResetUrl = '#';
        }

        return $baseResetUrl;
    }
}
