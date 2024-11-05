<?php

namespace App\Filament\User\Resources\ReportResource\Pages;

use App\Filament\User\Resources\ReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Parallax\FilamentComments\Actions\CommentsAction;

class ViewReport extends ViewRecord
{
    protected static string $resource = ReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CommentsAction::make()
        ];
    }
}
