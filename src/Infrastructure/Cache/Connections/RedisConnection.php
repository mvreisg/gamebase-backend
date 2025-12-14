<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Cache\Connections;

use Mvreisg\GamebaseBackend\Infrastructure\Environments\Dotenv\DotenvEnvironment;
use Mvreisg\GamebaseBackend\Infrastructure\Cache\Connections\Exceptions\RedisConnectionException;
use Predis\Client;

class RedisConnection
{
    public static function get(): Client
    {
        try {
            return new Client([
                'scheme' => DotenvEnvironment::get('REDIS_SCHEME'),
                'host' => DotenvEnvironment::get('REDIS_HOST'),
                'port' => DotenvEnvironment::get('REDIS_PORT'),
            ]);
        } catch (\Throwable $e) {
            throw new RedisConnectionException(
                "Redis Connection error: {$e->getMessage()}",
                $e
            );
        }
    }
}
