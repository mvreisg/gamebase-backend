<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Authorization\Exceptions;

class UnauthorizedException extends \Exception
{
    public function __construct()
    {
        parent::__construct("Unauthorized.");
    }
}
