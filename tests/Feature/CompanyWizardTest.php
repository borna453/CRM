<?php

use App\Filament\Resources\CompanyResource\Pages\CreateCompany;
use function Pest\Livewire\livewire;

it('correctly creates company and users', function () {
    $this->get($this->tenant->route('filament.admin.resources.companies.create'))->assertSuccessful();

    $formArray = [
        'coc_number' => '12345678',
        'name' => 'Cloudmazingtest company',
        'email' => 'companyemailtest@cloudmazing.nl',
        'phone_number' => '1234567890',
        'address' => 'Test address',
        'zip_code' => '1234AB',
        'city' => 'Test city',
        'users' => [
            [
                'first_name' => 'Test',
                'last_name' => 'User1',
                'email' => 'testuser1@cloudmazing.nl',
                'login_allowed' => true,
                'email_enabled' => true,
                'should_invite' => true,
            ],
            [
                'first_name' => 'Test',
                'last_name' => 'User2',
                'email' => 'testuser2@cloudmazing.nl',
                'login_allowed' => false,
                'email_enabled' => false,
                'should_invite' => false,
            ]
        ],
    ];

    livewire(CreateCompany::class)
        ->assertFormExists()
        ->set('data.users', null)
        ->assertFormFieldExists('name')
        ->assertFormFieldExists('users')
        ->assertSee(__('portal.clients.client'))
        ->assertSee(__('portal.users.contacts'))
        ->fillForm($formArray)
        ->assertFormSet($formArray)->call('create');

    $this->assertDatabaseHas('companies', [
        'coc_number' => '12345678',
        'name' => 'Cloudmazingtest company',
        'email' => 'companyemailtest@cloudmazing.nl',
        'phone_number' => '1234567890',
        'address' => 'Test address',
        'zip_code' => '1234AB',
        'city' => 'Test city',
    ]);

    $this->assertDatabaseHas('users', [
        'first_name' => 'Test',
        'last_name' => 'User1',
        'email' => 'testuser1@cloudmazing.nl',
        'login_allowed' => true,
        'email_enabled' => true,
        'should_invite' => true,
    ]);

    $this->assertDatabaseHas('users', [
        'first_name' => 'Test',
        'last_name' => 'User2',
        'email' => 'testuser2@cloudmazing.nl',
        'login_allowed' => false,
        'email_enabled' => false,
        'should_invite' => false,
    ]);
});

it('correctly populates company fields based on the CoC number', function (){
    $this->get($this->tenant->route('filament.admin.resources.companies.create'))->assertSuccessful();

    livewire(CreateCompany::class)
        ->assertFormFieldIsHidden('choose')
        ->assertFormComponentActionExists('coc_number', 'get_details')
        ->assertFormFieldExists('coc_number')
        ->fillForm([
            'coc_number' => '69599084',
        ])
        ->callFormComponentAction('coc_number', 'get_details')
        ->assertFormFieldIsVisible('choose')
        ->fillForm([
            'choose' => '69599084-000038509520'
        ])
        ->assertFormSet([
            'name' => 'Test EMZ Dagobert',
            'address' => 'Abebe Bikilalaan 17 M 1034WL Amsterdam',
            'city' => 'Amsterdam'
        ]);
});
