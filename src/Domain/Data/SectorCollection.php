<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Data;

class SectorCollection
{
    /**
     * @var Sector[]
     */
    private array $values;

    public function __construct(?array $values)
    {
        if (isset($values) === true) {
            $this->values = $values;
        } else {
            $this->values = [];
        }
    }

    public function add(Sector $value): bool
    {
        if (isset($value) === false) {
            return false;
        }
        $this->values[] = $value;
        return true;
    }

    public function exists(Id $id): bool
    {
        foreach ($this->values as $value) {
            if ($value->getIdValue() === $id->getValue()) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return Sector[]
     */
    public function fetchAll(): array
    {
        return $this->values;
    }

    public function findById(Id $id): ?Sector
    {
        foreach ($this->values as $value) {
            if ($value->getIdValue() === $id->getValue()) {
                return $value;
            }
        }
        return null;
    }

    public function findByName(Name $id): SectorCollection
    {
        $matches = new SectorCollection(null);
        foreach ($this->values as $value) {
            if ($value->getNameValue() === $id->getValue()) {
                $matches->add($value);
            }
        }
        return $matches;
    }

    public function replace(Id $id, Sector $new): void
    {
        foreach ($this->values as $key => $value) {
            if ($value->getIdValue() === $id->getValue()) {
                $this->values[$key] = $new;
                return;
            }
        }
    }

    public function remove(Id $id): bool
    {
        foreach ($this->values as $key => $value) {
            if ($value->getIdValue() === $id->getValue()) {
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
