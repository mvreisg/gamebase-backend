<?php

declare(strict_types=1);

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

    public function validateId()
    {
        if ($this->id <= 0) {
            throw new EntityInvalidValueException('id deve ser maior que 0!');
        }
    }

    public function validateName()
    {
        $this->name = trim($this->name);

        if ($this->name === '') {
            throw new EntityInvalidValueException('name está vazio!');
        }
    }
}
