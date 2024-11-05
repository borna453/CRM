<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Stancl\Tenancy\Database\Models\Domain;

class CloudmazingResetCommand extends Command
{
    protected $signature = 'reset:cloudmazing';

    protected $description = 'Reset all the Cloudmazing users';

    public function handle(): void
    {
        $this->info('Resetting Cloudmazing users...');
        User::where('email', 'like', '%@cloudmazing.nl')->get()->each(function (User $user) {
            $user->update([
                'password' => bcrypt('secret'),
            ]);
            $this->info("Reset password for {$user->email}");
        });

        $this->info('Resetting Tenant domains...');
        Domain::with('tenant')->each(function (Domain $domain) {
            $domain->update(['domain' => "{$domain->tenant_id}." . config('custom.central_domain')]);
            $this->info("Domain {$domain->domain} updated");
        });
        $this->info('Done!');
    }
}
