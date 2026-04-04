<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\User\ValueObject\Username\Exception;

class InvalidUsernameValueException extends \Exception
{
    public function __construct(string $value)
    {
        parent::__construct("Invalid username value: " . $value);
    }
}
