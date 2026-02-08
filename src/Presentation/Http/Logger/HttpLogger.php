<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Logger;

use Mvreisg\GamebaseBackend\Infrastructure\Environments\Dotenv\DotenvEnvironment;

class HttpLogger
{
    public static function logThrowable(string $name, string $message, int $line, string $file): void
    {
        $now = new \DateTimeImmutable();
        $now = $now->setTimezone(new \DateTimeZone(DotenvEnvironment::get("TIME_ZONE")));
        $path = PROJECT_ROOT . "/logs/http/error/error-" . $now->format("Y-m-d H:i:s") . ".log";
        $file = fopen($path, "a");
        fwrite($file, "[$name] $message in $file on line $line" . PHP_EOL);
        fclose($file);
    }
}
