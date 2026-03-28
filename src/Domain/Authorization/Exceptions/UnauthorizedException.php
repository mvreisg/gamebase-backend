<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Authorization\Exceptions;

class UnauthorizedException extends \DomainException
{
    public function __construct()
    {
        parent::__construct("Unauthorized");
    }
}
