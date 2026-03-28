<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Authentication\Exceptions;

class InvalidTokenException extends \DomainException
{
    public function __construct()
    {
        parent::__construct("Invalid token!");
    }
}
