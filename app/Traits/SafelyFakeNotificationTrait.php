<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

trait SafelyFakeNotificationTrait
{
    public static function safelyFake(callable $callback): void
    {
        DB::beginTransaction();

        Model::withoutEvents(function () use ($callback) {
            $callback();
        });

        DB::rollBack();
    }
}
