<?php

namespace App\Notifications;

use App\Enums\NotificationTypeEnum;
use App\Models\Company;
use App\Utils\Notifications\NotificationButtonHelper;
use App\Utils\Notifications\NotificationHelper;
use Filament\Notifications\Actions\Action;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class CompanyCreationReminder extends BaseNotification implements ShouldQueue
{
    public function __construct(
        private readonly Company $company
    )
    {
        $this->message = __('portal.notifications.company.creation_reminder');
        parent::__construct(['company' => $this->company]);
    }

    protected function getType(): string
    {
        return NotificationTypeEnum::COMPANY_CREATION_REMINDER->value;
    }

    public function via(object $notifiable): array
    {
        return NotificationHelper::getNotificationChannels($notifiable);
    }

    public function toDatabase(): array
    {
        return \Filament\Notifications\Notification::make()
            ->title(__('portal.notifications.company.subject'))
            ->body($this->message . " {$this->company->name}.")
            ->actions([
                Action::make('openCompany')
                    ->label(__('portal.notifications.company.view_company'))
                    ->url(NotificationHelper::generateTenantUrl($this->company->tenant_id, 'filament.admin.resources.companies.edit', ['record' => $this->company->id])),
            ])
            ->viewData(['model_id' => $this->company->id, 'model_type' => Company::class])
            ->getDatabaseMessage();
    }

    public function templateParams($buttonText = null, $preview = false): array
    {
        $buttonLabel = $buttonText ?? __('portal.notifications.company.view_company');

        if(!$preview){
            $companyUrl = NotificationHelper::generateTenantUrl(
                $this->company->tenant?->id,
                'filament.admin.resources.companies.edit',
                ['record' => $this->company->id]
            );
        }

        return [
            'company_name' => $this->company->name,
            'button' => NotificationButtonHelper::generateButtonHtml($buttonLabel, $companyUrl ?? '#'),
            ...$this->getTenantEmailInformation($this->company)
        ];
    }

    public static function templateParamsList(): array
    {
        return [
            'company_name' => __('portal.notifications.company.company_name'),
            'button' => __('portal.notifications.company.button'),
            ...self::getTenantEmailTemplateInformation()
        ];
    }

    public static function fake(): Notification
    {
        $notification = null;

        self::safelyFake(function () use (&$notification) {
            $notification = new self(Company::factory()->make([
                'name' => Company::main()->first()->name,
            ]));
        });

        return $notification;
    }
}
