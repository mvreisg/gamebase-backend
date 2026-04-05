<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\User\ValueObject\Password\Exception;

class InvalidPasswordValueException extends \Exception
{
    public function __construct(string $value)
    {
        parent::__construct("Invalid password value: " . $value);
    }
}
