<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock;

use Mvreisg\GamebaseBackend\Domain\Entities\GamePlatform;
use Mvreisg\GamebaseBackend\Domain\Repositories\GamePlatformRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\GameRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\PlatformRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseUnexistantRegisterException;

class MockGamePlatformRepository implements GamePlatformRepositoryInterface
{
    private array $data;
    private int $index;
    private GameRepositoryInterface $gameRepository;
    private PlatformRepositoryInterface $platformRepository;

    public function __construct(
        GameRepositoryInterface $gameRepository,
        PlatformRepositoryInterface $platformRepository
    ) {
        $this->data = [];
        $this->index = 0;
        $this->gameRepository = $gameRepository;
        $this->platformRepository = $platformRepository;
    }

    public function insert(GamePlatform $gamePlatform): GamePlatform
    {
        $gameId = $gamePlatform->getGameId();
        $game = $this->gameRepository->findById($gameId);
        if ($game === null) {
            throw new DatabaseUnexistantRegisterException(
                'O registro com o id ' . $gameId . ' não existe!'
            );
        }

        $platformId = $gamePlatform->getPlatformId();
        $platform = $this->platformRepository->findById($platformId);
        if ($platform === null) {
            throw new DatabaseUnexistantRegisterException(
                'O registro com o id ' . $platformId . ' não existe!'
            );
        }

        $this->index++;
        $id = $this->index;

        $gamePlatform->setId($id);
        $this->data[] = $gamePlatform;

        $newGamePlatform = new GamePlatform(
            $id,
            $gameId,
            $platformId
        );
        return $newGamePlatform;
    }

    public function update(GamePlatform $gamePlatform): bool
    {
        $gameId = $gamePlatform->getGameId();
        $game = $this->gameRepository->findById($gameId);
        if ($game === null) {
            throw new DatabaseUnexistantRegisterException(
                'O registro com o id ' . $gameId . ' não existe!'
            );
        }

        $platformId = $gamePlatform->getPlatformId();
        $platform = $this->platformRepository->findById($platformId);
        if ($platform === null) {
            throw new DatabaseUnexistantRegisterException(
                'O registro com o id ' . $platformId . ' não existe!'
            );
        }

        $idToUpdate = null;
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $gamePlatform->getId()) {
                $idToUpdate = $key;
            }
        }

        if ($idToUpdate === null) {
            throw new DatabaseUnexistantRegisterException(
                'O registro com o id ' . $idToUpdate . ' não existe!'
            );
        }

        $gamePlatformToUpdate = $this->data[$idToUpdate];

        $hasDifferentGameId =
            $gamePlatformToUpdate->getGameId() !== $gamePlatform->getGameId();

        $hasDifferentPlatformId =
            $gamePlatformToUpdate->getPlatformId() !== $gamePlatform->getPlatformId();

        $hasDifferences = $hasDifferentGameId || $hasDifferentPlatformId;

        if ($hasDifferences === false) {
            return false;
        }

        $gamePlatformToUpdate->setGameId($gamePlatform->getGameId());
        $gamePlatformToUpdate->setPlatformId($gamePlatform->getPlatformId());
        return true;

        $this->data[$idToUpdate] = $gamePlatformToUpdate;

        return true;
    }

    public function delete(GamePlatform $gamePlatform): bool
    {
        $idToDelete = null;
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $gamePlatform->getId()) {
                $idToDelete = $key;
                break;
            }
        }

        if ($idToDelete === null) {
            return false;
        }

        unset($this->data[$idToDelete]);
        return true;
    }

    /*
    public function setIsActive(int $id, bool $isActive): bool
    {
        $index = null;
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $id) {
                $index = $key;
            }
        }

        if ($index === null) {
            return false;
        }

        $findedGamePlatform = $this->data[$index];

        $changedSomething = $findedGamePlatform->getIsActive() !== $isActive;

        $this->data[$index]->setIsActive($isActive);

        return $changedSomething;
    }
    */

    public function findById(int $id): GamePlatform|null
    {
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $id) {
                return $value;
            }
        }
        return null;
    }

    public function findAll(): array
    {
        return $this->data;
    }
}
