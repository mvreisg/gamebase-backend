<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Permission\ValueObject\PermissionValue;

use Mvreisg\GamebaseBackend\Domain\Authorization\Permission\PermissionType;
use Mvreisg\GamebaseBackend\Domain\Permission\ValueObject\Exception\EmptyPermissionValueValueException;
use Mvreisg\GamebaseBackend\Domain\Permission\ValueObject\Exception\InvalidPermissionValueValueException;

class PermissionValue
{
    private PermissionType $value;

    public function __construct(string $value)
    {
        $this->value = $this->validate($value);
    }

    public static function from(PermissionType $type): self
    {
        return new self($type->value);
    }

    public static function create(string $value): self
    {
        return new self($value);
    }

    public function getValue(): PermissionType
    {
        return $this->value;
    }

    public function validate(string $value): PermissionType
    {
        $trimmed = trim($value);

        if ($trimmed === "") {
            throw new EmptyPermissionValueValueException();
        }

        $isInvalid = preg_match("/[^a-zA-Z0-9\_]/", $trimmed);
        if ($isInvalid) {
            throw new InvalidPermissionValueValueException(
                $trimmed
            );
        }

        $type = PermissionType::tryFrom($trimmed);
        if ($type === null) {
            throw new InvalidPermissionValueValueException(
                $trimmed
            );
        }

        return $type;
    }
}
