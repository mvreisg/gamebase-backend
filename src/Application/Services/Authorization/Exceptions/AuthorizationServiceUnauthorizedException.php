<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services\Authorization\Exceptions;

class AuthorizationServiceUnauthorizedException extends \DomainException
{
    public function __construct()
    {
        parent::__construct("Unauthorized");
    }
}
