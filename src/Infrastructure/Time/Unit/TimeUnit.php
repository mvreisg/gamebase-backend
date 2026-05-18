<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Time\Unit;

use Mvreisg\GamebaseBackend\Infrastructure\Time\Unit\Exception\TimeUnitException;

enum TimeUnit
{
    case Second;
    case Minute;
    case Hour;
    case Day;
    case Week;

    public static function getName(TimeUnit $timeUnit): string
    {
        switch ($timeUnit) {
            case TimeUnit::Second:
                return "second";
            case TimeUnit::Minute:
                return "minute";
            case TimeUnit::Hour:
                return "hour";
            case TimeUnit::Day:
                return "day";
            case TimeUnit::Week:
                return "week";
            default:
                throw new TimeUnitException("Untreated time unit: $timeUnit");
        }
    }
}
