<?php

use App\Filament\Owner\Resources\TenantResource\Pages\CreateTenant;
use App\Filament\Owner\Resources\TenantResource\Pages\ViewTenant;
use App\Filament\Owner\Resources\TenantResource\RelationManagers\UsersRelationManager;
use App\Models\Company;
use App\Models\Tenant;
use App\Models\User;
use RalphJSmit\Filament\Onboard\Http\Livewire\Wizard;
use Stancl\Tenancy\Database\Models\Domain;
use function Pest\Livewire\livewire;



it('correctly creates new tenants', function () {
    $this->actingAs($this->ownerUser);
    filament()->setCurrentPanel(filament()->getPanel('owner'));

    livewire(CreateTenant::class)
        ->assertFormExists()
        ->fillForm([
            'company.name' => 'Cloudmazingtest',
            'users.email' => 'test@cloudmazingtest.nl',
            'id' => 'cloudmazingtest',
        ])
        ->assertFormSet([
            'company.name' => 'Cloudmazingtest',
            'users.email' => 'test@cloudmazingtest.nl',
            'id' => 'cloudmazingtest',
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertRedirect(route('filament.owner.resources.tenants.view', ['record' => 'cloudmazingtest']));

    $this->assertDatabaseHas('tenants', [
        'id' => 'cloudmazingtest',
    ]);
    $this->assertDatabaseHas('companies', [
        'name' => 'Cloudmazingtest',
    ]);
    $this->assertDatabaseHas('users', [
        'email' => 'superadmin@cloudmazing.nl',
        'tenant_id' => 'cloudmazingtest',
    ]);
    $this->assertDatabaseHas('users', [
        'email' => 'test@cloudmazingtest.nl',
        'tenant_id' => 'cloudmazingtest',
    ]);
    $this->assertDatabaseHas('domains', [
        'domain' => 'cloudmazingtest.' . config('custom.central_domain'),
    ]);
});

it('correctly allows impersonation', function () {
    $this->actingAs($this->ownerUser);
    filament()->setCurrentPanel(filament()->getPanel('owner'));

    livewire(UsersRelationManager::class, [
        'ownerRecord' => $this->tenant,
        'pageClass' => ViewTenant::class
    ])->assertSuccessful()->assertCanSeeTableRecords($this->tenant->users);

    $component = livewire(UsersRelationManager::class, [
        'ownerRecord' => $this->tenant,
        'pageClass' => ViewTenant::class
    ])
    ->callTableAction('impersonation', $this->tenant->users->first()->id)->assertSuccessful()->assertRedirect();
});

it('opens the onboarding on first login', function (){
    $tenant = Tenant::create([
        'id' => 'cloudmazingtest',
    ]);

    Domain::create([
        'domain' => 'cloudmazingtest.' . config('custom.central_domain'),
        'tenant_id' => $tenant->id,
    ]);
    $user = User::create([
        'first_name' => 'Test',
        'email' => 'testadmin@cloudmazing.nl',
        'password' => bcrypt('password'),
        'tenant_id' => $tenant->id
    ])->assignRole(User::ADMIN);

    Company::create([
       'name' => 'Cloudmazingtest',
    ]);

    $formArray = [
        'userData.first_name' => 'Test',
        'userData.last_name' => 'Admin',
        'userData.email' => 'testadmin@cloudmazing.nl',
        'companyData.name' => 'Cloudmazingtest',
        'companyData.coc_number' => '12345678',
        'companyData.email' => 'testadmin@cloudmazing.nl',
        'companyData.phone_number' => '12345678',
        'companyData.address' => 'Teststreet 1',
        'companyData.zip_code' => '1234AB',
        'companyData.city' => 'Testcity',
        'emailSettings.from_name' => 'Cloudmazingtest',
        'emailSettings.footer' => 'Testfooter',
    ];

    $this->actingAs($user);

    $this->get($tenant->route('filament.admin.filament.onboard.onboard'))->assertSuccessful()->assertSeeLivewire(Wizard::class);

    livewire(Wizard::class, ['stepIdentifier' => 'widget::amend-user'])
        ->assertFormExists()
        ->assertFormFieldExists('userData.first_name')
        ->fillForm($formArray)
        ->assertFormSet($formArray)
        ->call('submit');

    $this->assertDatabaseHas('users', [
        'first_name' => 'Test',
        'email' => 'testadmin@cloudmazing.nl',
        'tenant_id' => 'cloudmazingtest',
    ]);

    $this->assertDatabaseHas('companies', [
        'name' => 'Cloudmazingtest',
    ]);

    $this->assertDatabaseHas('domains', [
        'domain' => 'cloudmazingtest.' . config('custom.central_domain'),
    ]);

    $this->assertDatabaseHas('tenants', [
        'id' => 'cloudmazingtest',
        'data->email->footer' => 'Testfooter',
        'data->email->from_name' => 'Cloudmazingtest'
    ]);
});
