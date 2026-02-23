<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Logs;

use Mvreisg\GamebaseBackend\Domain\Data\Calendar;

class Logger
{
    public static function logAppError(\Throwable $e): void
    {
        $path = PROJECT_ROOT . "/logs/error/app/error-" . Calendar::getNowWithTimezone()->format("Y-m-d H:i:s") . ".log";
        self::write($path, $e);
    }

    public static function logHttpError(\Throwable $e): void
    {
        $now = Calendar::getNowWithTimezone();
        $path = PROJECT_ROOT . "/logs/error/http/error-" . Calendar::getNowWithTimezone()->format("Y-m-d H:i:s") . ".log";
        self::write($path, $e);
    }

    private static function write(string $path, \Throwable $e): void
    {
        $file = fopen($path, "a");
        fwrite($file, "{$e->getMessage()} in {$e->getFile()} on line {$e->getLine()}." . PHP_EOL . PHP_EOL);
        foreach ($e->getTrace() as $trace) {
            fwrite($file, "File: {$trace["file"]} Line: {$trace["line"]} Function: {$trace["function"]}" . PHP_EOL);
        }
    }
}
