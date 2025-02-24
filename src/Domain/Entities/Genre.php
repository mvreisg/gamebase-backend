<?php

namespace Mvreisg\GamebaseBackend\Domain\Entities;

use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;

/**
 * Genre entity class.
 */
class Genre
{
    /**
     * @var int $id The Genre id.
     */
    private int $id;

    /**
     * @var string $name The Genre name.
     */
    private string $name;

    private bool $isActive;

    /**
     * Genre entity class constructor.
     * @param int $id [optional] The Genre id.
     * @param string $name [optional] The Genre name.
     * @return void
     */
    public function __construct(int $id = 0, string $name = '', bool $isActive = false)
    {
        $this->id = $id;
        $this->name = $name;
        $this->isActive = $isActive;
    }

    /**
     * Gets the Genre id.
     * @return int The Genre id.
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Sets the Genre id.
     * @param int $id The genre id.
     * @return void
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * Gets the Genre name.
     * @return string The Genre name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Sets the Genre name.
     * @param string $name The Genre name.
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
     * Method that validates the id of the Genre.
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
     * Method that validates the name of the Genre.
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
