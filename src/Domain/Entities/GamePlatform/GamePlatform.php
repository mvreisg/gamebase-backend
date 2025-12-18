<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Entities\GamePlatform;

use Mvreisg\GamebaseBackend\Domain\Entities\GamePlatform\Exceptions\GamePlatformInvalidGameIdException;
use Mvreisg\GamebaseBackend\Domain\Entities\GamePlatform\Exceptions\GamePlatformInvalidIdException;
use Mvreisg\GamebaseBackend\Domain\Entities\GamePlatform\Exceptions\GamePlatformInvalidPlatformIdException;

class GamePlatform
{
    private ?int $id;
    private int $platformId;
    private int $gameId;

    public function __construct(?int $id = null, int $platformId = 0, int $gameId = 0)
    {
        $this->id = $id;
        $this->platformId = $platformId;
        $this->gameId = $gameId;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
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
            throw new GamePlatformInvalidIdException(
                'The id must be greater than zero!'
            );
        }
    }

    public function validatePlatformId(): void
    {
        if ($this->platformId <= 0) {
            throw new GamePlatformInvalidPlatformIdException(
                'The platform id must be greater than zero!'
            );
        }
    }

    public function validateGameId(): void
    {
        if ($this->gameId <= 0) {
            throw new GamePlatformInvalidGameIdException(
                'The game id must be greater than zero!'
            );
        }
    }
}
