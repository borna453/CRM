<?php

namespace App\Filament\Resources\CompanyResource\Pages;

use App\Filament\Resources\CompanyResource;
use App\Models\Role;
use App\Models\User;
use App\Traits\RedirectToIndexTrait;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class CreateCompany extends CreateRecord
{
    use RedirectToIndexTrait;

    protected static string $resource = CompanyResource::class;

    protected static bool $canCreateAnother = false;

    protected function handleRecordCreation(array $data): Model
    {
        $usersData = $data['users'] ?? [];
        unset($data['users']);

        $company = parent::handleRecordCreation($data);

        foreach ($usersData as $userData) {

            if (isset($userData['first_name'])) {
                $user = $company->users()->create($userData);
                $user->assignRole(User::USER);
            }
        }

        return $company;
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()->extraAttributes(['type' => 'button', 'wire:click' => 'create']);
    }

    public function handleEnterKey()
    {
        $this->mountFormComponentAction('data.coc_number', 'get_details');
    }
}
