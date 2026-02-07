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

        $environment = $_SERVER["ENVIRONMENT"];
        $machine = $_SERVER["MACHINE"];

        $dotenv = Dotenv::createMutable(PROJECT_ROOT, ".env." . $environment . "." . $machine);
        $dotenv->load();
    }

    public static function get(string $key): mixed
    {
        $value = $_SERVER[$key];
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
