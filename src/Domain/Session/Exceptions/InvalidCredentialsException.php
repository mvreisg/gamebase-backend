<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Session\Exceptions;

class InvalidCredentialsException extends \DomainException
{
    public function __construct()
    {
        parent::__construct("Invalid credentials!");
    }
}
