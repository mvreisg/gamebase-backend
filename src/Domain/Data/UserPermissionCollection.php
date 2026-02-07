<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Data;

class UserPermissionCollection
{
    /**
     * @var UserPermission[]
     */
    private array $values;

    public function __construct()
    {
        $this->values = [];
    }

    public function add(UserPermission $value): bool
    {
        if (isset($value) === false) {
            return false;
        }
        $this->values[] = $value;
        return true;
    }

    /**
     * @return UserPermission[]
     */
    public function fetchAll(): array
    {
        return $this->values;
    }

    public function findById(Id $id): ?UserPermission
    {
        foreach ($this->values as $value) {
            if ($value->getIdValue() === $id->getValue()) {
                return $value;
            }
        }
        return null;
    }

    public function findAllByUserId(Id $userId): UserPermissionCollection
    {
        $matches = new UserPermissionCollection();
        foreach ($this->values as $value) {
            if ($value->getUserIdValue() === $userId->getValue()) {
                $matches->add($value);
            }
        }
        return $matches;
    }

    public function replace(Id $id, UserPermission $new): void
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
