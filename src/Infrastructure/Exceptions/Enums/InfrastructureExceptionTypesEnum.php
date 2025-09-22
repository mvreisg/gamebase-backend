<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Enums;

enum InfrastructureExceptionTypesEnum: int
{
    case JwtAuthentication = 1;
    case SodiumEncryption = 2;
    case DefuseEncryption = 3;
    case RedisCache = 4;
    case MariaDBRepository = 5;
    case MockRepository = 6;
    case Environment = 7;
}
