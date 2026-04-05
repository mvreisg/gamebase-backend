<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Genre\Entity\Collection;

use Mvreisg\GamebaseBackend\Domain\Genre\Entity\Genre;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Name\Name;

class GenreCollection
{
    /**
     * @var Genre[]
     */
    private array $values;

    public function __construct()
    {
        $this->values = [];
    }

    public function add(Genre $value): bool
    {
        if (isset($value) === false) {
            return false;
        }
        $this->values[] = $value;
        return true;
    }

    /**
     * @return Genre[]
     */
    public function fetchAll(): array
    {
        return $this->values;
    }

    public function findById(Id $id): ?Genre
    {
        foreach ($this->values as $value) {
            if ($value->getId()->getValue() === $id->getValue()) {
                return $value;
            }
        }
        return null;
    }

    public function findByName(Name $name): GenreCollection
    {
        $matches = new GenreCollection();
        foreach ($this->values as $value) {
            if ($value->getName()->getValue() === $name->getValue()) {
                $matches->add($value);
            }
        }
        return $matches;
    }

    public function replace(Id $id, Genre $new): void
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
