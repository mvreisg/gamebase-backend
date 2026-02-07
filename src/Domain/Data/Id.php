<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Data;

class Id
{
    private int $value;

    public function __construct(int $value)
    {
        $this->validate($value);
        $this->value = $value;
    }

    public static function make(int $value): self
    {
        return new self($value);
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function validate(int $value): void
    {
        if ($value <= 0) {
            throw new \InvalidArgumentException(
                "The id must be greater than zero!"
            );
        }
    }

    public function increment(int $amount)
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException(
                "The amount must be greater than zero!"
            );
        }
        $this->value += $amount;
    }
}
