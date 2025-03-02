<?php

namespace Mvreisg\GamebaseBackend\Domain\Entities;

use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;

/**
 * Game entity class.
 */
class Game
{
    /**
     * @var int $id The Game id.
     */
    private int $id;

    /**
     * @var string $name The Game name.
     */
    private string $name;

    private bool $isActive;

    /**
     * The Game class constructor.
     * @param int $id [optional] The Game id.
     * @param string $name [optional] The Game name;
     * @param array $genres [optional] The Game Genres objects list.
     * @param array $platforms [optional] The Game Genres objects list.
     * @return void
     */
    public function __construct(int $id = 0, string $name = '', bool $isActive = false)
    {
        $this->id = $id;
        $this->name = $name;
        $this->isActive = $isActive;
    }

    /**
     * The Game id getter.
     * @return int The Game id.
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * The Game id setter.
     * @param int $id The Game id.
     * @return void
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * The Game name getter.
     * @return string The Game name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * The Game name setter.
     * @param string $name The Game name.
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
     * Method to validate the id of the Game, throwing an exception if the id is invalid.
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
     * Method to validate the name of the Game, throwing an exception if the name is invalid.
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
