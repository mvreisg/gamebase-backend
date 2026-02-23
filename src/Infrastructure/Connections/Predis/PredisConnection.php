<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Connections\Predis;

use Mvreisg\GamebaseBackend\Infrastructure\Environments\Dotenv\DotenvEnvironment;
use Predis\Client;

class PredisConnection
{
    private Client $connection;

    public function __construct()
    {
        $this->connection = new Client([
            "scheme" => DotenvEnvironment::get("REDIS_SCHEME"),
            "host" => DotenvEnvironment::get("REDIS_HOST"),
            "port" => DotenvEnvironment::get("REDIS_PORT"),
        ]);
    }

    public static function make(): PredisConnection
    {
        return new self();
    }

    public function get(): Client
    {
        return $this->connection;
    }
}
