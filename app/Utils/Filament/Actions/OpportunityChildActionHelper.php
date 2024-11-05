<?php

namespace App\Utils\Filament\Actions;

class OpportunityChildActionHelper
{
    public static function getModalHeading($livewire)
    {
        if (property_exists($livewire, 'mountedTableActionsData') && $livewire->mountedTableActionsData) {
            return $livewire->mountedTableActionsData[0]['title'];
        }

        return $livewire->editModalFormState['title'] ?? $livewire->opportunity->title;
    }
}
