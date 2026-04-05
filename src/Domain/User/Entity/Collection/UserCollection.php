<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\User\Entity\Collection;

use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\User\Entity\User;
use Mvreisg\GamebaseBackend\Domain\User\ValueObject\Username\Username;

class UserCollection
{
    /**
     * @var User[]
     */
    private array $values;

    public function __construct()
    {
        $this->values = [];
    }

    public function add(User $value): bool
    {
        if (isset($value) === false) {
            return false;
        }
        $this->values[] = $value;
        return true;
    }

    /**
     * @return User[]
     */
    public function fetchAll(): array
    {
        return $this->values;
    }

    public function findById(Id $id): ?User
    {
        foreach ($this->values as $value) {
            if ($value->getId()->getValue() === $id->getValue()) {
                return $value;
            }
        }
        return null;
    }

    public function findByUsername(Username $username): ?User
    {
        foreach ($this->values as $value) {
            if ($value->getUsername()->getValue() === $username->getValue()) {
                return $value;
            }
        }
        return null;
    }

    public function findAllByUsername(Username $username): UserCollection
    {
        $matches = new UserCollection();
        foreach ($this->values as $value) {
            if ($value->getUsername()->getValue() === $username->getValue()) {
                $matches->add($value);
            }
        }
        return $matches;
    }

    public function replace(Id $id, User $new): void
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
