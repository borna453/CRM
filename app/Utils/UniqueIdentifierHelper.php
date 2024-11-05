<?php

namespace App\Utils;

class UniqueIdentifierHelper
{
    public static function getId(int $id): string
    {
        $domain = parse_url(config('app.url'), PHP_URL_HOST);

        return "{$domain}:{$id}";
    }
}
