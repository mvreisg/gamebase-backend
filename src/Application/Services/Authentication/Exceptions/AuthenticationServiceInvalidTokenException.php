<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services\Authentication\Exceptions;

class AuthenticationServiceInvalidTokenException extends \DomainException
{
    public function __construct()
    {
        parent::__construct("Invalid token!");
    }
}
