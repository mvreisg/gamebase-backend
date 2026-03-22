<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Logs;

use Mvreisg\GamebaseBackend\Domain\Entities\Calendar;

class Logger
{
    public static function logAppError(\Throwable $e): void
    {
        /*
        $directory = PROJECT_ROOT . "/logs/error/app";
        $filename = "error-" . Calendar::getNowWithTimezone()->format("Y-m-d H:i:s") . ".log";
        self::write($directory, $filename, $e);
        */
    }

    public static function logHttpError(\Throwable $e): void
    {
        /*
        $directory = PROJECT_ROOT . "/logs/error/http";
        $filename = "error-" . Calendar::getNowWithTimezone()->format("Y-m-d H:i:s") . ".log";
        self::write($directory, $filename, $e);
        */
    }

    private static function write(string $directory, string $filename, \Throwable $e): void
    {
        /*
        if (is_dir($directory) === false){
            mkdir($directory, 0777, true);
        }
        $file = fopen("$directory/$filename", "a");
        fwrite($file, "{$e->getMessage()} in {$e->getFile()} on line {$e->getLine()}." . PHP_EOL . PHP_EOL);
        foreach ($e->getTrace() as $trace) {
            fwrite($file, "File: {$trace["file"]} Line: {$trace["line"]} Function: {$trace["function"]}" . PHP_EOL);
        }
            */
    }
}
