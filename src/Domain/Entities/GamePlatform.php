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
    private int $id;

    /**
     * @var int $platformId The Platform id.
     */
    private int $platformId;

    /**
     * @var int $gameId The Game id.
     */
    private int $gameId;

    /**
     * Game Platform entity class constructor.
     * @param int $id The entity id.
     * @param int $platformId The Platform id.
     * @param int $gameId The Game id.
     * @return void
     */
    public function __construct(int $id = 0, int $platformId = 0, int $gameId = 0)
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
    public function setId(int $id)
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
    public function setPlatformId(int $platformId)
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
    public function setGameId(int $gameId)
    {
        $this->gameId = $gameId;
    }

    /**
     * Method that validates the entity id, throwing an exception if it is not valid.
     * @throws EntityInvalidValueException Throwed if the id is not valid.
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
     * Method that validates the Platform id, throwing an exception if it is not valid.
     * @throws EntityInvalidValueException Throwed if the id is not valid.
     * @return void
     */
    public function validatePlatformId(mixed $platformId)
    {
        if ($platformId === null) {
            throw new EntityInvalidValueException('O platformId é null!');
        }

        if (is_numeric($platformId) === false) {
            throw new EntityInvalidValueException('O platformId não é um número!');
        }

        if (is_string($platformId)) {
            throw new EntityInvalidValueException('O platformId é uma string!');
        }

        if (is_bool($platformId)) {
            throw new EntityInvalidValueException('O platformId é um valor booleano!');
        }

        if ($platformId < 1) {
            throw new EntityInvalidValueException('O platformId ' . $platformId . ' deve ser maior que 0.');
        }
    }

    /**
     * Method that validates the Game id, throwing an exception if it is not valid.
     * @throws EntityInvalidValueException Throwed if the id is not valid.
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
