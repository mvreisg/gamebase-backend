<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Entities;

use Mvreisg\GamebaseBackend\Domain\Entities\Exceptions\EntityException;

class EncodedPassword extends Password
{
    public function __construct(string $value)
    {
        parent::__construct($value);
    }

    public static function make(string $value): self
    {
        return new self($value);
    }

    public function validate(string $value): string
    {
        $trimmedValue = trim($value);

        if ($trimmedValue === "") {
            throw new EntityException(
                "The password is empty!"
            );
        }

        return $trimmedValue;
    }
}
