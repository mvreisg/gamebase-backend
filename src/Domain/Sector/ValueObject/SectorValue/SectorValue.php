<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Sector\ValueObject\SectorValue;

use Mvreisg\GamebaseBackend\Domain\Sector\ValueObject\Exception\EmptySectorValueValueException;
use Mvreisg\GamebaseBackend\Domain\Sector\ValueObject\Exception\InvalidSectorValueValueException;

class SectorValue
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
            throw new EmptySectorValueValueException();
        }

        $isInvalid = preg_match("/[^a-zA-Z0-9\_]/", $originalName);
        if ($isInvalid) {
            throw new InvalidSectorValueValueException(
                $originalName
            );
        }

        return $originalName;
    }
}
