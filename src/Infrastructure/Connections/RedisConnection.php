<?php

namespace Mvreisg\GamebaseBackend\Infrastructure\Connections;

use Predis\Client;

class RedisConnection
{
    public static function get(): Client
    {
        return new Client([
            'scheme' => $_SERVER['REDIS_SCHEME'],
            'host' => $_SERVER['REDIS_HOST'],
            'port' => $_SERVER['REDIS_PORT'],
        ]);
    }
}
