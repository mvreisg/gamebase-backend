<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Sector\ValueObject\SectorValue;

use Mvreisg\GamebaseBackend\Domain\Authorization\Sector\SectorType;
use Mvreisg\GamebaseBackend\Domain\Sector\ValueObject\Exception\EmptySectorValueValueException;
use Mvreisg\GamebaseBackend\Domain\Sector\ValueObject\Exception\InvalidSectorValueValueException;

class SectorValue
{
    private SectorType $value;

    public function __construct(string $value)
    {
        $this->value = $this->validate($value);
    }

    public static function from(SectorType $type): self
    {
        return new self($type->value);
    }

    public static function create(string $value): self
    {
        return new self($value);
    }

    public function getValue(): SectorType
    {
        return $this->value;
    }

    public function validate(string $value): SectorType
    {
        $trimmed = trim($value);

        if ($trimmed === "") {
            throw new EmptySectorValueValueException();
        }

        $isInvalid = preg_match("/[^a-zA-Z0-9\_]/", $trimmed);
        if ($isInvalid) {
            throw new InvalidSectorValueValueException(
                $trimmed
            );
        }

        $type = SectorType::tryFrom($trimmed);

        if ($type === null) {
            throw new InvalidSectorValueValueException(
                $trimmed
            );
        }

        return $type;
    }
}
