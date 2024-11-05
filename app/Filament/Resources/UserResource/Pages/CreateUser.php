<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\Company;
use App\Traits\RedirectToIndexTrait;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateUser extends CreateRecord
{
    use RedirectToIndexTrait;

    protected static string $resource = UserResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $user = parent::handleRecordCreation($data);

        if ($this->data['hasAdminPanelRole'] ?? false) {
            $user->update([
                'company_id' => Company::main()->first()->id,
            ]);
        }

        return $user;
    }
}
