<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Entities;

use Mvreisg\GamebaseBackend\Domain\Entities\Exceptions\EntityException;

class GamePlatform
{
    private ?Id $id;
    private Id $platformId;
    private Id $gameId;

    public function __construct(Id $gameId, Id $platformId)
    {
        $this->id = null;
        $this->gameId = $gameId;
        $this->platformId = $platformId;
    }

    public function setId(Id $id): void
    {
        $this->id = $id;
    }

    public function getId(): Id
    {
        if ($this->id === null) {
            throw new EntityException(
                "The id is null."
            );
        }
        return $this->id;
    }

    public function getPlatformId(): Id
    {
        if ($this->platformId === null) {
            throw new EntityException(
                "The platformId is null."
            );
        }
        return $this->platformId;
    }

    public function getGameId(): Id
    {
        if ($this->gameId === null) {
            throw new EntityException(
                "The gameId is null."
            );
        }
        return $this->gameId;
    }
}
