<?php

use App\Filament\Owner\Resources\TenantResource\Pages\CreateTenant;
use App\Models\Tenant;
use Filament\Facades\Filament;
use Stancl\Tenancy\Database\Models\Domain;
use function Pest\Livewire\livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('owner'));
});

it('creates users etc', function () {
    $this->actingAs($this->ownerUser);

    livewire(CreateTenant::class)
        ->assertFormExists()
        ->fillForm([
            'id' => 'test-company',
            'company.name' => 'Test Company',
            'users.email' => 'borna@cloudmazing.nl'
        ])
        ->assertFormSet(
            ['id' => 'test-company',
                'company.name' => 'Test Company',
                'users.email' => 'borna@cloudmazing.nl'
            ]
        )
        ->call('create')
        ->assertHasNoFormErrors();

    $tenant = Tenant::find('test-company');

    tenancy()->initialize($tenant);

    expect($tenant->company['name'])->toBe('Test Company')
        ->and($tenant->users['email'])->toBe('borna@cloudmazing.nl')
        ->and(Domain::where('tenant_id', $tenant->id)->exists())->toBeTrue();
});
