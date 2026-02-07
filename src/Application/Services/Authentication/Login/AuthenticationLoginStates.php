<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services\Authentication\Login;

enum AuthenticationLoginStates
{
    case New;
    case Existing;
}
