<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Phpsa\FilamentPasswordReveal\Password;
use Tapp\FilamentTimezoneField\Enums\Region;
use Tapp\FilamentTimezoneField\Forms\Components\TimezoneSelect;

class EditProfile extends \Filament\Pages\Auth\EditProfile
{
    public function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('first_name')
                ->label(__('portal.profile.info.first_name'))
                ->required()
                ->maxLength(255)
                ->autofocus(),
            TextInput::make('last_name')
                ->label(__('portal.profile.info.last_name'))
                ->required()
                ->maxLength(255),

            $this->getEmailFormComponent(),

            TextInput::make('old_password')
                ->label(__('portal.previous_password'))
                ->revealable(filament()->arePasswordsRevealable())
                ->password()
                ->required(function (Forms\Get $get){
                    return filled($get('password'));
                })
                ->rules(['required_with:password', 'current_password'])
                ->dehydrated(false),

            $this->getPasswordFormComponent(),
            $this->getPasswordConfirmationFormComponent(),

            Forms\Components\Select::make('locale')
                ->label(__('portal.users.locale'))
                ->options(collect(config('app.supported_locale'))->mapWithKeys(fn ($locale) => [$locale => __("portal.locales.$locale")]))
                ->default(config('app.supported_locale')[0]),

            TimezoneSelect::make('timezone')
                ->label(__('portal.users.timezone'))
                ->searchable()
                ->byRegion([\DateTimeZone::ALL]),
        ]);
    }

    public function mount(): void
    {
        /** @var \App\Models\User $actor */
        $actor = $this->getUser();

        $this->form->fill([
            'first_name' => $actor->first_name,
            'last_name' => $actor->last_name,
            'email' => $actor->email,
            'locale' => $actor->locale ?: config('app.supported_locale')[0],
            'timezone' => $actor->timezone ?: 'Europe/Amsterdam',
        ]);
    }

    protected function afterSave(): void
    {
        if ($this->getUser()->wasChanged('locale')) {
            $this->redirect(self::getUrl());
        }
        $this->mount();
    }
}
