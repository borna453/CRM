<?php

namespace App\Filament\Pages;

use App\Enums\Features;
use App\Models\Feature;
use App\Utils\Notifications\NotificationHelper;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;

class RinkelSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.rinkel-settings';

    protected static ?string $navigationGroup = 'Settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    protected function getFormStatePath(): ?string
    {
        return 'data';
    }

    protected function getFormSchema(): array
    {
        $tenant = auth()->user()?->tenant;

        $webhooks = [
            [
                'title' => __('portal.calls.incoming_call'),
                'url'   => NotificationHelper::generateTenantUrl(tenant()->id, 'webhooks.rinkel.incoming-call', ['key' => $tenant->rinkel]),
            ],
            [
                'title' => __('portal.calls.outgoing_call'),
                'url'   => NotificationHelper::generateTenantUrl(tenant()->id, 'webhooks.rinkel.outgoing-call', ['key' => $tenant->rinkel]),
            ],
            [
                'title' => __('portal.calls.call_start'),
                'url'   => NotificationHelper::generateTenantUrl(tenant()->id, 'webhooks.rinkel.call-start', ['key' => $tenant->rinkel]),
            ],
            [
                'title' => __('portal.calls.call_end'),
                'url'   => NotificationHelper::generateTenantUrl(tenant()->id, 'webhooks.rinkel.call-end', ['key' => $tenant->rinkel]),
            ],
            [
                'title' => __('portal.calls.call_insights'),
                'url'   => NotificationHelper::generateTenantUrl(tenant()->id, 'webhooks.rinkel.call-insights', ['key' => $tenant->rinkel]),
            ],
        ];

        return array_map(function ($webhook) {
            return Section::make()
                          ->schema([
                              Placeholder::make('title')
                                         ->content($webhook['title'])
                                         ->hiddenLabel(),
                              TextInput::make('url' . $webhook['title'])
                                       ->disabled()
                                       ->default($webhook['url'])
                                       ->hiddenLabel()
                                       ->dehydrated(false)
//                                       ->suffixAction(CopyAction::make()
//                                                                ->copyable(fn($component) => $component->getDefaultState())),
                          ]);
        }, $webhooks);
    }

    public static function canAccess(): bool
    {
        return Feature::isActive(Features::RINKEL);
        //auth()->user()->can('viewAny', CallEventPolicy::class);
    }
}
