<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Environments\Dotenv;

use Dotenv\Dotenv;

class DotenvEnvironment
{
    public static function load(): void
    {
        $dotenv = Dotenv::createMutable(PROJECT_ROOT, ".env");
        $dotenv->load();
        $dotenv->required(["ENVIRONMENT", "MACHINE"])->required();

        $environment = $_ENV["ENVIRONMENT"];
        $machine = $_ENV["MACHINE"];                
        $dotenv = Dotenv::createMutable(
            PROJECT_ROOT,
            sprintf(
                ".env.%s.%s",
                $environment,
                $machine
            )
        );
        $dotenv->load();
    }

    public static function get(string $key): mixed
    {
        $value = $_ENV[$key];
        return $value;
    }

    public static function getArray(string $key, string $separator): array
    {
        $values = explode(
            $separator,
            self::get($key)
        );
        return $values;
    }
}
