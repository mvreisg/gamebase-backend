<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services\Authentication\Enums;

enum AuthenticationLoginExistanceStatesEnum: string
{
    case New = "New";
    case Existing = "Existing";
}
