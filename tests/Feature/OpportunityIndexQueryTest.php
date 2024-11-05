<?php

use App\Models\Company;
use App\Models\Opportunity;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $companies = Company::factory(10)->create();
    $companies->each(function ($company) {
        Opportunity::factory(1)->create([
            'company_id' => $company->id
        ]);
    });
});

it('loads the index page without errors', function () {
    $response = $this->get($this->tenant->route('filament.admin.resources.opportunities.index'));

    $response->assertStatus(200);
});
