<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Data;

class Calendar
{
    public static function getDateTimeImmutableBasedOnTimestamp(int $timestamp): \DateTimeImmutable
    {
        return (new \DateTimeImmutable())->setTimestamp($timestamp);
    }
}
