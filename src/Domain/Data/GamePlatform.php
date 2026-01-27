<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Data;

class GamePlatform
{
    private ?Id $id;
    private Id $platformId;
    private Id $gameId;

    public function __construct(?Id $id = null, Id $platformId, Id $gameId)
    {
        $this->id = $id;
        $this->platformId = $platformId;
        $this->gameId = $gameId;
    }

    public function getIdValue(): int
    {
        if ($this->id === null) {
            throw new \InvalidArgumentException(
                "The id is null."
            );
        }
        return $this->id->getValue();
    }

    public function getPlatformIdValue(): int
    {
        if ($this->platformId === null) {
            throw new \InvalidArgumentException(
                "The platformId is null."
            );
        }
        return $this->platformId->getValue();
    }

    public function getGameIdValue(): int
    {
        if ($this->gameId === null) {
            throw new \InvalidArgumentException(
                "The gameId is null."
            );
        }
        return $this->gameId->getValue();
    }
}
