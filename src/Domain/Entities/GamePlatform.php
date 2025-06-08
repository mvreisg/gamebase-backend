<?php

declare(strict_types=1);

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

    public function validateId(): void
    {
        if ($this->id <= 0) {
            throw new EntityInvalidValueException('O id deve ser maior que zero!');
        }
    }

    public function validatePlatformId()
    {
        if ($this->platformId <= 0) {
            throw new EntityInvalidValueException('O platformId deve ser maior que zero!');
        }
    }

    public function validateGameId()
    {
        if ($this->gameId <= 0) {
            throw new EntityInvalidValueException('O gameId deve ser maior que zero!');
        }
    }
}
