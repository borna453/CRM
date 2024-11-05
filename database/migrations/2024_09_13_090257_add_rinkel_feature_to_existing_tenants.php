<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        \App\Models\Tenant::all()->each(function (\App\Models\Tenant $tenant) {
            $tenant->rinkel = uuid_create();
            $tenant->save();


            if(\App\Models\Feature::where('tenant_id', $tenant->id)
                ->where('name', 'rinkel')
                ->where('scope', 'global')
                ->exists()) {
                return;
            }

            \App\Models\Feature::create([
                'tenant_id' => $tenant->id,
                'name' => 'rinkel',
                'scope' => 'global',
                'value' => false,
            ]);

        });
    }

    public function down(): void
    {
        \App\Models\Feature::where('name', 'rinkel')
            ->where('scope', 'global')
            ->delete();
    }
};
