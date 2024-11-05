<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.logo_light_mode');
        $this->migrator->add('general.logo_dark_mode');
        $this->migrator->add('general.favicon');
    }
};
