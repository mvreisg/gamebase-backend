<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Environments\Dotenv;

use Dotenv\Dotenv;
use Mvreisg\GamebaseBackend\Infrastructure\Environments\Dotenv\Exceptions\DotenvEnvironmentException;

class DotenvEnvironment
{
    public static function load(): void
    {
        try {
            $dotenv = Dotenv::createMutable(PROJECT_ROOT, '.env');
            $dotenv->load();
            $dotenv->required(["ENVIRONMENT", "MACHINE"])->required();

            $environment = $_SERVER["ENVIRONMENT"];
            $machine = $_SERVER["MACHINE"];

            $dotenv = Dotenv::createMutable(PROJECT_ROOT, '.env.' . $environment . '.' . $machine);
            $dotenv->load();
        } catch (\Throwable $e) {
            throw new DotenvEnvironmentException(
                "Dotenv environment error: {$e->getMessage()}",
                $e
            );
        }
    }

    public static function get(string $key): mixed
    {
        try {
            $value = $_SERVER[$key];
            return $value;
        } catch (\Throwable $e) {
            throw new DotenvEnvironmentException(
                "Dotenv environment error: {$e->getMessage()}",
                $e
            );
        }
    }

    public static function getArray(string $key, string $separator): array
    {
        try {
            $values = explode(
                $separator,
                self::get($key)
            );
            return $values;
        } catch (\Throwable $e) {
            throw new DotenvEnvironmentException(
                "Dotenv environment error: {$e->getMessage()}",
                $e
            );
        }
    }
}
