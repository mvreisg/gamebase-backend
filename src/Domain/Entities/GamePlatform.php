<?php

namespace Mvreisg\GamebaseBackend\Domain\Entities;

use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;

class GamePlatform
{
    private int $id;

    private int $platformId;

    private int $gameId;

    public function __construct(int $id = 0, int $platformId = 0, int $gameId = 0)
    {
        $this->id = $id;
        $this->platformId = $platformId;
        $this->gameId = $gameId;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId(int $id)
    {
        $this->id = $id;
    }

    public function getPlatformId()
    {
        return $this->platformId;
    }

    public function setPlatformId(int $platformId)
    {
        $this->platformId = $platformId;
    }

    public function getGameId()
    {
        return $this->gameId;
    }

    public function setGameId(int $gameId)
    {
        $this->gameId = $gameId;
    }

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
