<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Cache\Redis\Connection;

use Mvreisg\GamebaseBackend\Infrastructure\Environments\Dotenv\DotenvEnvironment;
use Predis\Client;

class RedisConnection
{
    private static ?self $instance = null;
    private Client $client;

    public static function get(): Client
    {
        return self::getInstance()->getClient();
    }

    private static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function getClient(): Client
    {
        if (isset($this->client) === false) {
            $this->client = new Client([
                "scheme" => DotenvEnvironment::get("REDIS_SCHEME"),
                "host" => DotenvEnvironment::get("REDIS_HOST"),
                "port" => DotenvEnvironment::get("REDIS_PORT"),
            ]);
        }
        return $this->client;
    }
}
