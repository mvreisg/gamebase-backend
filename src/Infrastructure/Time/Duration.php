<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Time;

class Duration
{
    public const ONE_SECOND = 1;
    public const SECONDS_PER_MINUTE = 60;
    public const MINUTES_PER_HOUR = 60;
    public const HOURS_PER_DAY = 24;
    public const ONE_MINUTE_IN_SECONDS = self::ONE_SECOND * self::SECONDS_PER_MINUTE;
    public const ONE_HOUR_IN_SECONDS = self::ONE_MINUTE_IN_SECONDS * self::MINUTES_PER_HOUR;
    public const ONE_DAY_IN_SECONDS = self::ONE_HOUR_IN_SECONDS * self::HOURS_PER_DAY;
}
