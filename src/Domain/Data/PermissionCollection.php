<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Data;

class PermissionCollection
{
    /**
     * @var Permission[]
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

    public function add(Permission $value): bool
    {
        if (isset($value) === false) {
            return false;
        }
        $this->values[] = $value;
        return true;
    }

    /**
     * @return Permission[]
     */
    public function fetchAll(): array
    {
        return $this->values;
    }

    public function findById(Id $id): ?Permission
    {
        foreach ($this->values as $value) {
            if ($value->getIdValue() === $id->getValue()) {
                return $value;
            }
        }
        return null;
    }

    public function findByName(Name $id): PermissionCollection
    {
        $matches = new PermissionCollection(null);
        foreach ($this->values as $value) {
            if ($value->getNameValue() === $id->getValue()) {
                $matches->add($value);
            }
        }
        return $matches;
    }

    public function replace(Id $id, Permission $new): void
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
