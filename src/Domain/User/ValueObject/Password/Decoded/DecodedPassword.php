<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\User\ValueObject\Password\Decoded;

use Mvreisg\GamebaseBackend\Domain\User\ValueObject\Password\Exception\EmptyPasswordValueException;
use Mvreisg\GamebaseBackend\Domain\User\ValueObject\Password\Exception\InvalidPasswordValueException;
use Mvreisg\GamebaseBackend\Domain\User\ValueObject\Password\Password;

class DecodedPassword extends Password
{
    public function __construct(string $value)
    {
        parent::__construct($value);
    }

    public static function create(string $value): self
    {
        return new self($value);
    }

    public function validate(string $value): string
    {
        $trimmedValue = trim($value);

        if ($trimmedValue === "") {
            throw new EmptyPasswordValueException();
        }

        $isInvalid = preg_match("/[^a-zA-Z0-9]/", $trimmedValue);
        if ($isInvalid) {
            throw new InvalidPasswordValueException(
                $trimmedValue
            );
        }

        return $trimmedValue;
    }
}
