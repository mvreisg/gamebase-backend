<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Cache\Enums;

enum CacheInterfaceDeletionStatesEnum
{
    case Success;
    case Unexistant;
    case Error;
}
