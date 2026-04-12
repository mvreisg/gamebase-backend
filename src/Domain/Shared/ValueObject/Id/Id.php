<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id;

use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Exception\InvalidIdValueException;

class Id
{
    private int $value;

    public function __construct(int $value)
    {
        $this->validate($value);
        $this->value = $value;
    }

    public static function create(int $value): self
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
            throw new InvalidIdValueException(
                $value
            );
        }
    }
}
