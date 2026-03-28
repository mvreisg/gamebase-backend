<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Entities;

abstract class Password
{
    private string $value;

    public function __construct(string $value)
    {
        $this->value = $this->validate($value);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    abstract public function validate(string $value): string;
}
