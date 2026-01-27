<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Entities;

class HttpHeader
{
    private string $key;
    private string $value;

    public function __construct(?string $full, ?string $key = null, ?string $value = null)
    {
        if ($full === null) {
            $this->key = $key;
            $this->value = $value;
        } else {
            $parts = explode(":", $full);
            $this->key = trim($parts[0]);
            $this->value = trim($parts[1]);
        }
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getFull(): string
    {
        return "{$this->getKey()}: {$this->getValue()}";
    }
}
