<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services\Session\Login;

enum SessionLoginStates
{
    case New;
    case Existing;
}
