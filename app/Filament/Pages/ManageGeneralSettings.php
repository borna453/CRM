<?php

namespace App\Filament\Pages;

use App\Enums\Permissions;
use App\Settings\GeneralSettings;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;
use Illuminate\Contracts\Support\Htmlable;

class ManageGeneralSettings extends ManageTenantConfig
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?int $navigationSort = 1;
    public static function getNavigationGroup(): ?string
    {
        return 'Settings';
    }

    public static function getNavigationLabel(): string
    {
        return __('portal.general_settings');
    }

    public function getTitle(): string|Htmlable
    {
        return self::getNavigationLabel();
    }

    public function form(Form $form): Form
    {
        return parent::form($form)
            ->schema([
                Forms\Components\FileUpload::make('logo_light_mode')
                    ->image()
                    ->previewable()
                    ->rules('image'),
                Forms\Components\FileUpload::make('logo_dark_mode')
                    ->image()
                    ->previewable()
                    ->rules('image'),
                Forms\Components\FileUpload::make('favicon')
                    ->image()
                    ->previewable()
                    ->rules('image'),
            ]);
    }

    protected function group(): ?string
    {
        return 'general';
    }

    public static function canAccess(): bool
    {
        return auth()->user()->hasTenantPermissionTo(Permissions::VIEW_MANAGE_GENERAL_SETTINGS->value);
    }
}
