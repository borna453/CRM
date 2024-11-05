<?php

namespace App\Observers;

use App\Models\Feature;
use App\Models\Label;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\TenantWelcomeEmailNotification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class TenantObserver
{
    public function creating(Tenant $tenant): void
    {
        $tenant->id = strtolower($tenant->id);
    }

    public function created(Tenant $tenant): void
    {
        User::withoutEvents(function () use ($tenant) {
            return User::create([
                'first_name' => User::SUPERADMIN,
                'email' => 'superadmin@cloudmazing.nl',
                'password' => bcrypt(Str::random()),
                'tenant_id' => $tenant->id,
                'login_allowed' => true,
                'email_enabled' => false,
                'should_invite' => false,
            ])->assignRole(User::SUPERADMIN);
        });

        Label::create([
            'tenant_id' => $tenant->id,
            'name' => 'Open',
            'color' => 'blue',
            'show_on_board' => true,
            'order_column' => 1,
        ]);
        Label::create([
            'tenant_id' => $tenant->id,
            'name' => 'Doing',
            'color' => 'yellow',
            'show_on_board' => true,
            'order_column' => 2,
        ]);
        Label::create([
            'tenant_id' => $tenant->id,
            'name' => 'Done',
            'color' => 'green',
            'show_on_board' => true,
            'finished_state' => true,
            'order_column' => 3,
        ]);
        Label::create([
            'name' => 'Archived',
            'color' => 'slate',
            'show_on_board' => false,
            'order_column' => 4,
            'tenant_id' => $tenant->id,
            'should_archive' => true
        ]);
        $tenant->update([
           'rinkel' => uuid_create()
        ]);
    }
}
