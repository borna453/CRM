<?php

// config for Cloudmazing/FilamentEmailLog
return [

    'resource' => [
        'class' => App\Filament\Resources\EmailResource::class,
        'group' => '',
        'sort' => null,
    ],

    /**
     * Define the numbers of days to keep the emails in the log database
     */
    'keep_email_for_days' => 90,

];
