<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Authentication\Enums;

enum AuthenticationTimesEnum: string
{
    case OneDay = "OneDay";
    case OneWeek = "OneWeek";
}
