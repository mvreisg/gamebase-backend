<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Session\Exceptions;

class InvalidCredentialsException extends \Exception
{
    public function __construct()
    {
        parent::__construct("Invalid credentials.");
    }
}
