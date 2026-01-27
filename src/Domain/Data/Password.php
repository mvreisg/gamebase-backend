<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Data;

class Password
{
    private string $value;

    public function __construct(string $value)
    {
        $this->value = $this->validate($value);
    }

    public static function make(string $value): self
    {
        return new self($value);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function alterValue(string $value)
    {
        $this->value = $this->validate($value);
    }

    public function validate(string $value): string
    {
        $trimmedValue = trim($value);

        if ($trimmedValue === "") {
            throw new \InvalidArgumentException(
                "The password is empty!"
            );
        }

        $isInvalid = preg_match("/[^a-zA-Z0-9]/", $trimmedValue);
        if ($isInvalid) {
            throw new \InvalidArgumentException(
                "The password is invalid!"
            );
        }

        return $trimmedValue;
    }
}
