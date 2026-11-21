<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\User\ValueObject\Password;

use Mvreisg\GamebaseBackend\Domain\User\ValueObject\Password\Exception\EmptyPasswordValueException;

class Password
{
    private string $value;

    public function __construct(string $value)
    {
        $this->value = $this->validate($value);
    }

    public static function create(string $value): self
    {
        return new self($value);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function validate(string $value): string
    {
        $trimmedValue = trim($value);

        if ($trimmedValue === "") {
            throw new EmptyPasswordValueException();
        }

        return $trimmedValue;
    }
}
