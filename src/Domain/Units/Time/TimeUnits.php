<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Units\Time;

enum TimeUnits: string
{
    case Second = "second";
    case Minute = "minute";
    case Hour = "hour";
    case Day = "day";
    case Week = "week";
}
