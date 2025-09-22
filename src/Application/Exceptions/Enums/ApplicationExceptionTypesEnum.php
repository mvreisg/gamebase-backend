<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Exceptions\Enums;

enum ApplicationExceptionTypesEnum: int
{
    case Authentication = 1;
    case Repository = 2;
}
