<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\GamePlatform\Entity;

use Mvreisg\GamebaseBackend\Domain\Game\Entity\Game;
use Mvreisg\GamebaseBackend\Domain\Platform\Entity\Platform;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;

class GamePlatform
{
    private Id $id;
    private Game $game;
    private Platform $platform;

    public function __construct(
        Id $id,
        Game $game,
        Platform $platform
    ) {
        $this->id = $id;
        $this->game = $game;
        $this->platform = $platform;
    }

    public static function create(
        Id $id,
        Game $game,
        Platform $platform
    ): self {
        return new self(
            $id,
            $game,
            $platform
        );
    }

    public function getId(): Id
    {
        return $this->id;
    }

    public function getGame(): Game
    {
        return $this->game;
    }

    public function getPlatform(): Platform
    {
        return $this->platform;
    }
}
