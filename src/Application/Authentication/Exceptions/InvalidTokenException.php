<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Authentication\Exceptions;

class InvalidTokenException extends \Exception
{
    public function __construct()
    {
        parent::__construct("Invalid token.");
    }
}
