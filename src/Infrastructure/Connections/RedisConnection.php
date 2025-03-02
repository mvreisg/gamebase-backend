<?php

namespace Mvreisg\GamebaseBackend\Infrastructure\Connections;

use Predis\Client;

class RedisConnection
{
    public static function get(): Client
    {
        return new Client([
            'scheme' => 'tcp',
            'host' => $_SERVER['REDIS_HOST'],
            'port' => 6379,
        ]);
    }
}
