<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\User\ValueObject\Password\Exception;

class EmptyPasswordValueException extends \DomainException
{
    public function __construct()
    {
        parent::__construct("Empty password value.");
    }
}
