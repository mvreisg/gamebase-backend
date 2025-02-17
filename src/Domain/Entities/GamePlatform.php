<?php

namespace Mvreisg\GamebaseBackend\Domain\Entities;

use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;

/**
 * Game Platform entity class.
 */
class GamePlatform
{
    /**
     * @var int $id The entity id.
     */
    private mixed $id;

    /**
     * @var int $platformId The Platform id.
     */
    private mixed $platformId;

    /**
     * @var int $gameId The Game id.
     */
    private mixed $gameId;

    /**
     * Game Platform entity class constructor.
     * @param int $id The entity id.
     * @param int $platformId The Platform id.
     * @param int $gameId The Game id.
     * @return void
     */
    public function __construct(mixed $id = 0, mixed $platformId = 0, mixed $gameId = 0)
    {
        $this->id = $id;
        $this->platformId = $platformId;
        $this->gameId = $gameId;
    }

    /**
     * Gets the entity id.
     * @return int The entity id.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the entity id.
     * @param int $id The entity id.
     * @return void
     */
    public function setId(mixed $id)
    {
        $this->id = $id;
    }

    /**
     * Gets the Platform id.
     * @return int The Platform id.
     */
    public function getPlatformId()
    {
        return $this->platformId;
    }

    /**
     * Sets the Platform id.
     * @param int $id The Platform id.
     * @return void
     */
    public function setPlatformId(mixed $platformId)
    {
        $this->platformId = $platformId;
    }

    /**
     * Gets the Game id.
     * @return int The Game id.
     */
    public function getGameId()
    {
        return $this->gameId;
    }

    /**
     * Sets the Game id.
     * @param int $id The Game id.
     * @return void
     */
    public function setGameId(mixed $gameId)
    {
        $this->gameId = $gameId;
    }

    /**
     * Method that validates the entity id, throwing an exception if it is not valid.
     * @throws EntityInvalidValueException Throwed if the id is not valid.
     * @return void
     */
    public function validateId()
    {
        if ($this->id === null) {
            throw new EntityInvalidValueException('O id é null');
        }

        if (is_string($this->id) && is_numeric($this->id) === false) {
            throw new EntityInvalidValueException('O id é inválido.');
        }

        if ($this->id < 1) {
            throw new EntityInvalidValueException('O id ' . $this->id . ' é menor que um.');
        }
    }

    /**
     * Method that validates the Platform id, throwing an exception if it is not valid.
     * @throws EntityInvalidValueException Throwed if the id is not valid.
     * @return void
     */
    public function validatePlatformId()
    {
        if ($this->platformId === null) {
            throw new EntityInvalidValueException('O platformId é null');
        }

        if (is_string($this->platformId) && is_numeric($this->platformId) === false) {
            throw new EntityInvalidValueException('O platformId é inválido.');
        }

        if ($this->platformId < 1) {
            throw new EntityInvalidValueException('O platformId ' . $this->platformId . ' é menor que um.');
        }
    }

    /**
     * Method that validates the Game id, throwing an exception if it is not valid.
     * @throws EntityInvalidValueException Throwed if the id is not valid.
     * @return void
     */
    public function validateGameId()
    {
        if ($this->gameId === null) {
            throw new EntityInvalidValueException('O gameId é null');
        }

        if (is_string($this->gameId) && is_numeric($this->gameId) === false) {
            throw new EntityInvalidValueException('O gameId é inválido.');
        }

        if ($this->gameId < 1) {
            throw new EntityInvalidValueException('O gameId ' . $this->gameId . ' é menor que um.');
        }
    }
}
