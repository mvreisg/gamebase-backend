<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Entities;

use Mvreisg\GamebaseBackend\Domain\Exceptions\Entities\EntityInvalidValueException;

class GamePlatformEntity
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

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getPlatformId(): int
    {
        return $this->platformId;
    }

    public function setPlatformId(int $platformId): void
    {
        $this->platformId = $platformId;
    }

    public function getGameId(): int
    {
        return $this->gameId;
    }

    public function setGameId(int $gameId): void
    {
        $this->gameId = $gameId;
    }

    public function validateId(): void
    {
        if ($this->id <= 0) {
            throw new EntityInvalidValueException(
                'The id must be greater than zero!'
            );
        }
    }

    public function validatePlatformId(): void
    {
        if ($this->platformId <= 0) {
            throw new EntityInvalidValueException(
                'The platform id must be greater than zero!'
            );
        }
    }

    public function validateGameId(): void
    {
        if ($this->gameId <= 0) {
            throw new EntityInvalidValueException(
                'The game id must be greater than zero!'
            );
        }
    }
}
