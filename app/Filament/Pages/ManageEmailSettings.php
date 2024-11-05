<?php

namespace App\Filament\Pages;

use App\Enums\OnboardingTypes;
use App\Enums\Permissions;
use App\Mail\TestMail;
use App\Models\Onboarding;
use App\Utils\Filament\Actions\OnboardingActionAttributeHelper;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Mail;

class ManageEmailSettings extends ManageTenantConfig
{
    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    protected static ?int $navigationSort = 2;


    public $isOnboarding = false;

    public function mount(): void
    {
        parent::mount();

        $this->isOnboarding = \Request::get('onboard_edit_email_settings') === '1';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Settings';
    }

    public static function getNavigationLabel(): string
    {
        return __('portal.email_settings.title');
    }

    public function getTitle(): string|Htmlable
    {
        return self::getNavigationLabel();
    }

    public function form(Form $form): Form
    {
        return parent::form($form)
            ->schema([
                Forms\Components\Toggle::make('custom_server')
                    ->label(__('portal.email_settings.custom_server'))
                    ->columnSpanFull()
                    ->reactive(),
                Forms\Components\TextInput::make('host')
                    ->label(__('portal.email_settings.host'))
                    ->visible(fn (Forms\Get $get) => $get('custom_server'))
                    ->required()
                    ->placeholder('smtp.example.com'),
                Forms\Components\TextInput::make('port')
                    ->label(__('portal.email_settings.port'))
                    ->visible(fn (Forms\Get $get) => $get('custom_server'))
                    ->required()
                    ->integer()
                    ->placeholder(587),
                Forms\Components\TextInput::make('from_address')
                    ->label(__('portal.email_settings.from_address'))
                    ->visible(fn (Forms\Get $get) => $get('custom_server'))
                    ->required()
                    ->email()
                    ->placeholder(auth()->user()->company->email),
                Forms\Components\TextInput::make('username')
                    ->label(__('portal.email_settings.username'))
                    ->visible(fn (Forms\Get $get) => $get('custom_server'))
                    ->required()
                    ->placeholder('smtp'),
                Forms\Components\TextInput::make('password')
                    ->label(__('portal.email_settings.password'))
                    ->visible(fn (Forms\Get $get) => $get('custom_server'))
                    ->required()
                    ->password()
                    ->placeholder('password'),
                Forms\Components\TextInput::make('from_name')
                    ->label(__('portal.email_settings.from_name'))
                    ->placeholder(auth()->user()->company->name),
                Forms\Components\RichEditor::make('footer')
                    ->label(__('portal.email_settings.footer'))
                    ->columnSpanFull(),
            ]);
    }

    public function getFormActions(): array
    {
        return array_merge(parent::getFormActions(), [
            Action::make('test')
                ->label(__('portal.email_settings.test'))
                ->form([
                    Forms\Components\TextInput::make('email')
                        ->label(__('portal.email'))
                        ->default(auth()->user()->email)
                        ->required()
                        ->email()
                ])
                ->action(function (array $data) {
                    return Mail::to($data['email'])->send(new TestMail());
                })
        ]);
    }

    protected function group(): ?string
    {
        return 'email';
    }

    public static function canAccess(): bool
    {
        return auth()->user()->hasTenantPermissionTo(Permissions::VIEW_MANAGE_EMAIL_SETTINGS->value);
    }
}
