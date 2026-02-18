<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Logger;

use Mvreisg\GamebaseBackend\Infrastructure\Environments\Dotenv\DotenvEnvironment;

class HttpLogger
{
    public static function logThrowable(\Throwable $e): void
    {
        $now = new \DateTimeImmutable();
        $now = $now->setTimezone(new \DateTimeZone(DotenvEnvironment::get("TIME_ZONE")));
        $path = PROJECT_ROOT . "/logs/http/error/error-" . $now->format("Y-m-d H:i:s") . ".log";
        $file = fopen($path, "a");
        fwrite($file, "{$e->getMessage()} in {$e->getFile()} on line {$e->getLine()}." . PHP_EOL . PHP_EOL);
        foreach ($e->getTrace() as $trace) {
            fwrite($file, "File: {$trace["file"]} Line: {$trace["line"]} Function: {$trace["function"]}" . PHP_EOL);
        }
    }
}
