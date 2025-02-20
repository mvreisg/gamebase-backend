<?php

namespace Mvreisg\GamebaseBackend\Domain\Entities;

use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;

/**
 * Game Genre entity class.
 */
class GameGenre
{
    /**
     * @var int $id The id of the entity.
     */
    private int $id;

    /**
     * @var int $genreId The Genre id of the entity.
     */
    private int $genreId;

    /**
     * @var int $gameId The Game id of the entity.
     */
    private int $gameId;

    /**
     * Game Genre entity class constructor.
     * @param int $id [Optional] - The id of the entity.
     * @param int $genreId [Optional] - The Genre id of the entity.
     * @param int $gameId [Optional] - The Game id of the entity.
     * @return void
     */
    public function __construct(int $id = 0, int $genreId = 0, int $gameId = 0)
    {
        $this->id = $id;
        $this->genreId = $genreId;
        $this->gameId = $gameId;
    }

    /**
     * Gets the id of the entity.
     * @return int The id of the entity.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the id of the entity.
     * @param int $id The id of the entity.
     * @return void
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }

    /**
     * Gets the Genre id of the entity.
     * @return int The Genre id of the entity.
     */
    public function getGenreId()
    {
        return $this->genreId;
    }

    /**
     * Sets the Genre id of the entity.
     * @param int $genreId The Genre id of the entity.
     * @return void
     */
    public function setGenreId(int $genreId)
    {
        $this->genreId = $genreId;
    }

    /**
     * Gets the Game id of the entity.
     * @return int The Game id of the entity.
     */
    public function getGameId()
    {
        return $this->gameId;
    }

    /**
     * Sets the Game id of the entity.
     * @param int $gameId The Game id of the entity.
     * @return void
     */
    public function setGameId(int $gameId)
    {
        $this->gameId = $gameId;
    }

    /**
     * Validates the id of the entity, throwing an exception if it is invalid.
     * @throws EntityInvalidValueException Throwed if the id is invalid.
     * @return void
     */
    public function validateId(mixed $id)
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
     * Validates the Genre id of the entity, throwing an exception if it is invalid.
     * @throws EntityInvalidValueException Throwed if the Genre id is invalid.
     * @return void
     */
    public function validateGenreId(mixed $genreId)
    {
        if ($genreId === null) {
            throw new EntityInvalidValueException('O genreId é null!');
        }

        if (is_numeric($genreId) === false) {
            throw new EntityInvalidValueException('O genreId não é um número!');
        }

        if (is_string($genreId)) {
            throw new EntityInvalidValueException('O genreId é uma string!');
        }

        if (is_bool($genreId)) {
            throw new EntityInvalidValueException('O genreId é um valor booleano!');
        }

        if ($genreId < 1) {
            throw new EntityInvalidValueException('O genreId ' . $genreId . ' deve ser maior que 0.');
        }
    }

    /**
     * Validates the Game id of the entity, throwing an exception if it is invalid.
     * @throws EntityInvalidValueException Throwed if the Game id is invalid.
     * @return void
     */
    public function validateGameId(mixed $gameId)
    {
        if ($gameId === null) {
            throw new EntityInvalidValueException('O gameId é null!');
        }

        if (is_numeric($gameId) === false) {
            throw new EntityInvalidValueException('O gameId não é um número!');
        }

        if (is_string($gameId)) {
            throw new EntityInvalidValueException('O gameId é uma string!');
        }

        if (is_bool($gameId)) {
            throw new EntityInvalidValueException('O gameId é um valor booleano!');
        }

        if ($gameId < 1) {
            throw new EntityInvalidValueException('O gameId ' . $gameId . ' deve ser maior que 0.');
        }
    }
}
