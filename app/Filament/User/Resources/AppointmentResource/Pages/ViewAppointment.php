<?php

namespace App\Filament\User\Resources\AppointmentResource\Pages;

use App\Filament\CustomActions\AppointmentCommentAction;
use App\Filament\User\Resources\AppointmentResource;
use Cassandra\Custom;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Parallax\FilamentComments\Actions\CommentsAction;

class ViewAppointment extends ViewRecord
{
    protected static string $resource = AppointmentResource::class;

    protected static string $view = 'user-view-appointment';

    protected function getHeaderActions(): array
    {
        return [
            AppointmentCommentAction::make()->visible(fn($record) => $record->report)
        ];
    }
}
