<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Data;

class GameGenreCollection
{
    /**
     * @var GameGenre[]
     */
    private array $values;

    public function __construct()
    {
        $this->values = [];
    }

    public function add(GameGenre $value): bool
    {
        if (isset($value) === false) {
            return false;
        }
        $this->values[] = $value;
        return true;
    }

    public function fetchAll(): array
    {
        return $this->values;
    }

    public function findById(Id $id): ?GameGenre
    {
        foreach ($this->values as $value) {
            if ($value->getIdValue() === $id->getValue()) {
                return $value;
            }
        }
        return null;
    }

    public function replace(Id $id, GameGenre $new): void
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
        return count($this->values) === 0;
    }
}
