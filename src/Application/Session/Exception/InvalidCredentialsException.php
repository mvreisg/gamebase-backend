<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Session\Exception;

class InvalidCredentialsException extends \Exception
{
    public function __construct()
    {
        parent::__construct(
            "Invalid credentials."
        );
    }
}
