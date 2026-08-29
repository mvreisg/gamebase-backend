<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Logger\Monolog;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

class MonologLoggerFactory
{
    public static function create(): LoggerInterface
    {
        $logger = new Logger(
            "gamebase_backend"
        );

        $handler = new StreamHandler(
            PROJECT_ROOT . "/logs/app.log",
            Level::Debug
        );

        $formatter = new LineFormatter(
            null,
            "Y-m-d H:i:s",
            true,
            true
        );

        $handler->setFormatter($formatter);

        $logger->pushHandler($handler);

        return $logger;
    }
}
