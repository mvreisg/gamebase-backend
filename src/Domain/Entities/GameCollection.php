<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Entities;

class GameCollection
{
    /**
     * @var Game[]
     */
    private array $values;

    public function __construct()
    {
        $this->values = [];
    }

    public function add(Game $value): bool
    {
        if (isset($value) === false) {
            return false;
        }
        $this->values[] = $value;
        return true;
    }

    /**
     * @return Game[]
     */
    public function fetchAll(): array
    {
        return $this->values;
    }

    public function findById(Id $id): ?Game
    {
        foreach ($this->values as $value) {
            if ($value->getId()->getValue() === $id->getValue()) {
                return $value;
            }
        }
        return null;
    }

    public function findByName(Name $id): GameCollection
    {
        $matches = new GameCollection();
        foreach ($this->values as $value) {
            if ($value->getName()->getValue() === $id->getValue()) {
                $matches->add($value);
            }
        }
        return $matches;
    }

    public function replace(Id $id, Game $new): void
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
