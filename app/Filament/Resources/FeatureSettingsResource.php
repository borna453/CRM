<?php

namespace App\Filament\Resources;

use App\Enums\Features;
use App\Enums\Permissions;
use App\Filament\Resources\FeatureSettingsResource\Pages;
use App\Models\Feature;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class FeatureSettingsResource extends Resource
{
    protected static ?string $model = Feature::class;

    protected static ?string $slug = 'feature-settings';

    protected static ?string $navigationIcon = 'heroicon-o-light-bulb';

    protected static ?int $navigationSort = 3;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('portal.name'))
                    ->formatStateUsing(function ($record){
                        return Features::getLabel($record->name);
                    }),

                ToggleColumn::make('value')
                    ->disabled(fn() => ! auth()->user()->can('update', Feature::class))
                    ->label(__('portal.active')),
            ])
            ->paginated(false);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFeatureSettings::route('/'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Settings';
    }

    public static function getNavigationLabel(): string
    {
        return __('portal.feature_settings.label');
    }

    public static function getModelLabel(): string
    {
        return __('portal.feature_settings.feature');
    }

    public static function getPluralModelLabel(): string
    {
        return __('portal.feature_settings.features');
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->can('viewAny', Feature::class);
    }
}
