<?php

namespace App\Notifications;

use App\Enums\NotificationTypeEnum;
use App\Models\Tenant;
use App\Models\User;
use App\Utils\Notifications\NotificationButtonHelper;
use Filament\Facades\Filament;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TenantWelcomeEmailNotification extends BaseNotification implements ShouldQueue
{
    public function __construct(
        private readonly Tenant $tenant,
        private readonly User $user
    )
    {
        $this->message = __('portal.notifications.tenant.welcome');
        parent::__construct(['tenant' => $this->tenant, 'user' => $this->user]);
    }

    protected function getType(): string
    {
        return NotificationTypeEnum::TENANT_WELCOME_EMAIL->value;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function templateParams($buttonText = null, $preview = false): array
    {
        $buttonLabel = $buttonText ?? __('portal.notifications.tenant.reset_password');

        return [
            'user_name' => $this->user->first_name ?? '',
            'user_last_name' => $this->user->last_name ?? '',
            'user_name_full' => $this->user->name ?? '',
            'user_email' => $this->user->email ?? '',
            'tenant_email_from' => config('app.name'),
            'button' => NotificationButtonHelper::generateButtonHtml($buttonLabel, $this->resetUrl($this->user, $preview))
        ];
    }

    public static function templateParamsList(): array
    {
        return [
            'user_name_full' => __('portal.notifications.params.user_name_full'),
            'user_name' => __('portal.notifications.params.user_name'),
            'user_last_name' => __('portal.notifications.params.user_last_name'),
            'user_email' => __('portal.notifications.params.user_email'),
            'tenant_email_from' => __('portal.notifications.params.tenant_email_from'),
            'button' => __('portal.notifications.params.button'),
        ];
    }

    public static function fake(): Notification
    {
        $notification = null;

        self::safelyFake(function () use (&$notification){
            $notification = new self(
                Tenant::factory()::preview(),
                User::factory()::preview()
            );
        });

        return $notification;
    }

    protected function resetUrl(mixed $notifiable, $preview = false): string
    {
        request()->headers->set('HOST', $this->tenant->getHost());

        $token = app('auth.password.broker')->createToken($notifiable);

        $baseResetUrl = Filament::getPanel('admin')->getResetPasswordUrl($token, $notifiable);

        if($preview){
            $baseResetUrl = '#';
        }

        return $baseResetUrl;
    }
}
