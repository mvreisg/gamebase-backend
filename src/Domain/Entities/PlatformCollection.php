<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Entities;

class PlatformCollection
{
    /**
     * @var Platform[]
     */
    private array $values;

    public function __construct()
    {
        $this->values = [];
    }

    public function add(Platform $value): bool
    {
        if (isset($value) === false) {
            return false;
        }
        $this->values[] = $value;
        return true;
    }

    /**
     * @return Platform[]
     */
    public function fetchAll(): array
    {
        return $this->values;
    }

    public function findById(Id $id): ?Platform
    {
        foreach ($this->values as $value) {
            if ($value->getId()->getValue() === $id->getValue()) {
                return $value;
            }
        }
        return null;
    }

    public function findByName(Name $id): PlatformCollection
    {
        $matches = new PlatformCollection();
        foreach ($this->values as $value) {
            if ($value->getName()->getValue() === $id->getValue()) {
                $matches->add($value);
            }
        }
        return $matches;
    }

    public function replace(Id $id, Platform $new): void
    {
        foreach ($this->values as $key => $value) {
            if ($value->getId()->getValue() === $id->getValue()) {
                $this->values[$key] = $new;
                return;
            }
        }
    }

    public function remove(Id $id): bool
    {
        foreach ($this->values as $key => $value) {
            if ($value->getId()->getValue() === $id->getValue()) {
                isset($this->values[$key]);
                return true;
            }
        }
        return false;
    }

    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }

    public function count(): int
    {
        return count($this->values);
    }
}
