<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Data;

class Name
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

    public function validate(string $value): string
    {
        $originalName = trim($value);

        if ($originalName === "") {
            throw new \InvalidArgumentException(
                "The name is empty!"
            );
        }

        $isInvalid = preg_match("/[^a-zA-Z0-9]/", $originalName);
        if ($isInvalid) {
            throw new \InvalidArgumentException(
                "The name is invalid!"
            );
        }

        return $originalName;
    }
}
