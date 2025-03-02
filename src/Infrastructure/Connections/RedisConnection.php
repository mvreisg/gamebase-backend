<?php

namespace Mvreisg\GamebaseBackend\Infrastructure\Connections;

use Predis\Client;

class RedisConnection
{
    public static function get(): Client
    {
        return new Client([
            'scheme' => 'tcp',
            'host' => '172.24.105.188',
            'port' => 6379,
        ]);
    }
}
