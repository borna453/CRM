<?php

namespace App\Filament\User\Resources\TaskResource\Pages;

use App\Filament\User\Resources\TaskResource;
use App\Traits\RedirectToIndexTrait;
use Filament\Resources\Pages\CreateRecord;

class CreateTask extends CreateRecord
{
    use RedirectToIndexTrait;

    protected static string $resource = TaskResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->user()->id;

        return $data;
    }
}
