<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Data;

use Mvreisg\GamebaseBackend\Infrastructure\Environments\Dotenv\DotenvEnvironment;

class Calendar
{
    public static function getDateTimeImmutableBasedOnTimestamp(int $timestamp): \DateTimeImmutable
    {
        return (new \DateTimeImmutable())->setTimestamp($timestamp);
    }

    public static function getNowWithTimezone(): \DateTimeImmutable
    {
        return new \DateTimeImmutable()
            ->setTimezone(
                new \DateTimeZone(
                    DotenvEnvironment::get("TIME_ZONE")
                )
            );
    }
}
