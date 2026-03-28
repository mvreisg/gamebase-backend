<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Environments\Dotenv;

use Dotenv\Dotenv;

class DotenvEnvironment
{
    public static function load(): void
    {
        $environment = $_ENV["ENVIRONMENT"] ?? "development";
        $machine = $_ENV["MACHINE"] ?? "local";

        $dotenv = Dotenv::createImmutable(
            PROJECT_ROOT,
            sprintf(
                ".env.%s.%s",
                $environment,
                $machine
            )
        );
        $dotenv->load();
    }
}
