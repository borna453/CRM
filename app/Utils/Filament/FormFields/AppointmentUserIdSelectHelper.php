<?php

namespace App\Utils\Filament\FormFields;

use App\Filament\Resources\UserResource;
use App\Models\Company;
use App\Models\User;
use App\Traits\CombineFirstAndLastNameTrait;
use Filament\Forms\Components\Select;

class AppointmentUserIdSelectHelper
{
    public static function userSelectField()
    {
        return Select::make('user_id')
            ->label(__('portal.users.user'))
            ->relationship('user', 'name')
            ->options(User::role(User::USER)->pluck('name', 'id'))
            ->createOptionForm(UserResource::getWizardSchema())
            ->reactive()
            ->createOptionAction(function ($action) {
                return $action->modalWidth('5xl')->action(function ($get, $data, $set){
                    $company = null;

                    if (isset($data['name'])) {
                        $company = Company::create([
                            'coc_number' => $data['coc_number'],
                            'name' => $data['name'],
                            'email' => $data['email'],
                            'phone_number' => $data['phone_number'],
                            'address' => $data['address'],
                            'zip_code' => $data['zip_code'],
                            'city' => $data['city'],
                        ]);
                    }

                    $password = isset($data['user']['password']) ? bcrypt($data['user']['password']) : null;

                    unset($data['user']['password_confirmation']);
                    $user = User::create([
                        'first_name' => $data['user']['first_name'],
                        'last_name' => $data['user']['last_name'],
                        'email' => $data['user']['email'],
                        'password' => $password,
                        'company_id' => $company?->id ?? $data['user']['company_id'] ?? null,
                        'login_allowed' => $data['user']['login_allowed'],
                        'email_enabled' => $data['user']['email_enabled'],
                        'should_invite' => $data['user']['should_invite']
                    ])->assignRole(User::USER);

                    $set('user_id', $user->id);
                });
            })
            ->searchable()
            ->required();
    }
}
