<?php

namespace Mvreisg\GamebaseBackend\Domain\Entities;

use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;

class Permission
{
    private int $id;
    private string $name;
    private bool $isActive;

    public function __construct(int $id = 0, string $name = '', bool $isActive = false)
    {
        $this->id = $id;
        $this->name = $name;
        $this->isActive = $isActive;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    public function validateId(mixed $id)
    {
        if ($id === null) {
            throw new EntityInvalidValueException('id é null!');
        }

        if (is_iterable($id)) {
            throw new EntityInvalidValueException('id é array!');
        }

        if (is_string($id)) {
            throw new EntityInvalidValueException('id é string!');
        }

        if (is_bool($id)) {
            throw new EntityInvalidValueException('id é bool!');
        }

        if ($id <= 0) {
            throw new EntityInvalidValueException('id deve ser maior que 0!');
        }
    }

    public function validateName(mixed $name)
    {
        if ($name === null) {
            throw new EntityInvalidValueException('name é null!');
        }

        if (is_string($name) === false) {
            throw new EntityInvalidValueException('name não é string!');
        }

        $name = trim($name);

        if ($name === '') {
            throw new EntityInvalidValueException('name está vazio!');
        }
    }

    public function validateIsActive(mixed $isActive): void
    {
        if ($isActive === null) {
            throw new EntityInvalidValueException('isActive é null!');
        }

        if (is_iterable($isActive)) {
            throw new EntityInvalidValueException('isActive é array!');
        }

        if (is_string($isActive)) {
            throw new EntityInvalidValueException('isActive é uma string!');
        }

        if (is_numeric($isActive)) {
            throw new EntityInvalidValueException('isActive é numérico!');
        }
    }
}
