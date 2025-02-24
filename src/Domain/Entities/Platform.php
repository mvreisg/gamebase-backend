<?php

namespace Mvreisg\GamebaseBackend\Domain\Entities;

use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;

/**
 * Platform entity class.
 */
class Platform
{
    /**
     * @var int $id The Platform id.
     */
    private int $id;

    /**
     * @var string $name The Platform name.
     */
    private string $name;

    private bool $isActive;

    /**
     * Platform entity class constructor.
     * @param int $id [optional] The Platform id.
     * @param string $name [optional] The Platform name.
     * @return void
     */
    public function __construct(int $id = 0, string $name = '', bool $isActive = false)
    {
        $this->id = $id;
        $this->name = $name;
        $this->isActive = $isActive;
    }

    /**
     * Gets the id of the Platform.
     * @return int The Platform id.
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Sets the id of the Platform.
     * @param int $id The Platform id.
     * @return void
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * Gets the name of the Platform.
     * @return string The name of the Platform.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Sets the name of the platform.
     * @param string $name The platform name.
     * @return void
     */
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

    /**
     * Validates if the id is valid.
     * @throws EntityInvalidValueException Throwed if the id is invalid.
     */
    public function validateId(mixed $id): void
    {
        if ($id === null) {
            throw new EntityInvalidValueException('O id é null!');
        }

        if (is_numeric($id) === false) {
            throw new EntityInvalidValueException('O id não é um número!');
        }

        if (is_string($id)) {
            throw new EntityInvalidValueException('O id é uma string!');
        }

        if (is_bool($id)) {
            throw new EntityInvalidValueException('O id é um valor booleano!');
        }

        if ($id < 1) {
            throw new EntityInvalidValueException('O id ' . $id . ' deve ser maior que 0.');
        }
    }

    /**
     * Validates the name of the Platform.
     * @throws EntityInvalidValueException Throwed if the name is invalid.
     */
    public function validateName(mixed $name): void
    {
        if ($name === null) {
            throw new EntityInvalidValueException('O nome é null.');
        }

        if (is_string($name) === false) {
            throw new EntityInvalidValueException('O nome não é uma string.');
        }

        $name = trim($name);

        if ($name === '') {
            throw new EntityInvalidValueException('O nome está vazio.');
        }
    }

    public function validateIsActive(mixed $isActive): void
    {
        if ($isActive === null) {
            throw new EntityInvalidValueException('isActive é null!');
        }

        if (is_string($isActive)) {
            throw new EntityInvalidValueException('isActive é uma string!');
        }

        if (is_numeric($isActive)) {
            throw new EntityInvalidValueException('isActive é numérico!');
        }
    }
}
