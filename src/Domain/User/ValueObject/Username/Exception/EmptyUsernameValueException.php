<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\User\ValueObject\Username\Exception;

class EmptyUsernameValueException extends \DomainException
{
    public function __construct()
    {
        parent::__construct("Empty username value.");
    }
}
