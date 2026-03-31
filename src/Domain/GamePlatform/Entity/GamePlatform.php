<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\GamePlatform\Entity;

use Mvreisg\GamebaseBackend\Domain\Shared\Exception\NullIdException;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;

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
            throw new NullIdException(
                GamePlatform::class
            );
        }
        return $this->id;
    }

    public function getPlatformId(): Id
    {
        return $this->platformId;
    }

    public function getGameId(): Id
    {
        return $this->gameId;
    }
}
