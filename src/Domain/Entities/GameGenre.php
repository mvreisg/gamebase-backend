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
    public function validateId()
    {
        if ($this->id < 1) {
            throw new EntityInvalidValueException('O id ' . $this->id . ' é menor que um.');
        }
    }

    /**
     * Validates the Genre id of the entity, throwing an exception if it is invalid.
     * @throws EntityInvalidValueException Throwed if the Genre id is invalid.
     * @return void
     */
    public function validateGenreId()
    {
        if ($this->genreId < 1) {
            throw new EntityInvalidValueException('O genreId ' . $this->genreId . ' é menor que um.');
        }
    }

    /**
     * Validates the Game id of the entity, throwing an exception if it is invalid.
     * @throws EntityInvalidValueException Throwed if the Game id is invalid.
     * @return void
     */
    public function validateGameId()
    {
        if ($this->gameId < 1) {
            throw new EntityInvalidValueException('O gameId ' . $this->gameId . ' é menor que um.');
        }
    }
}
