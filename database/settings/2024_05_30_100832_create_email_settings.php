<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('email.custom_server', false);
        $this->migrator->add('email.host');
        $this->migrator->add('email.port');
        $this->migrator->add('email.username');
        $this->migrator->add('email.password');

        $this->migrator->add('email.footer');
    }
};
