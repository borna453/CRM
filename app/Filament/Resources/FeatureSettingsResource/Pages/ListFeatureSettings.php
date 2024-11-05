<?php

namespace App\Filament\Resources\FeatureSettingsResource\Pages;

use App\Filament\Resources\FeatureSettingsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFeatureSettings extends ListRecords
{
    protected static string $resource = FeatureSettingsResource::class;
}
