<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Units\Time;

enum TimeUnits
{
    case Second;
    case Minute;
    case Hour;
    case Day;
    case Week;

    public static function getName(TimeUnits $timeUnit): string
    {
        switch ($timeUnit) {
            case TimeUnits::Second:
                return "second";
            case TimeUnits::Minute:
                return "minute";
            case TimeUnits::Hour:
                return "hour";
            case TimeUnits::Day:
                return "day";
            case TimeUnits::Week:
                return "week";
            default:
                throw new \DomainException("Untreated time unit: $timeUnit");
        }
    }
}
