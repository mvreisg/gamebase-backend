<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Data;

class SectorPermissionCollection
{
    /**
     * @var SectorPermission[]
     */
    private array $values;

    public function __construct()
    {
        $this->values = [];
    }

    public function add(SectorPermission $value): bool
    {
        if (isset($value) === false) {
            return false;
        }
        $this->values[] = $value;
        return true;
    }

    /**
     * @return SectorPermission[]
     */
    public function fetchAll(): array
    {
        return $this->values;
    }

    public function findById(Id $id): ?SectorPermission
    {
        foreach ($this->values as $value) {
            if ($value->getIdValue() === $id->getValue()) {
                return $value;
            }
        }
        return null;
    }

    public function findAllByPermissionId(Id $permissionId): SectorPermissionCollection
    {
        $matches = new SectorPermissionCollection();
        foreach ($this->values as $value) {
            if ($value->getPermissionIdValue() === $permissionId->getValue()) {
                $matches->add($value);
            }
        }
        return $matches;
    }

    public function replace(Id $id, SectorPermission $new): void
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
