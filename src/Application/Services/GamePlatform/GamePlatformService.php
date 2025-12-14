<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services\GamePlatform;

use Mvreisg\GamebaseBackend\Application\Services\GamePlatform\Exceptions\GamePlatformServiceInvalidGameIdException;
use Mvreisg\GamebaseBackend\Application\Services\GamePlatform\Exceptions\GamePlatformServiceInvalidIdException;
use Mvreisg\GamebaseBackend\Application\Services\GamePlatform\Exceptions\GamePlatformServiceInvalidPlatformIdException;
use Mvreisg\GamebaseBackend\Application\Services\GamePlatform\Exceptions\GamePlatformServiceUnexistantGameException;
use Mvreisg\GamebaseBackend\Application\Services\GamePlatform\Exceptions\GamePlatformServiceUnexistantGamePlatformException;
use Mvreisg\GamebaseBackend\Application\Services\GamePlatform\Exceptions\GamePlatformServiceUnexistantPlatformException;
use Mvreisg\GamebaseBackend\Domain\Entities\GamePlatform\GamePlatform;
use Mvreisg\GamebaseBackend\Domain\Entities\GamePlatform\Exceptions\GamePlatformInvalidGameIdException;
use Mvreisg\GamebaseBackend\Domain\Entities\GamePlatform\Exceptions\GamePlatformInvalidIdException;
use Mvreisg\GamebaseBackend\Domain\Entities\GamePlatform\Exceptions\GamePlatformInvalidPlatformIdException;
use Mvreisg\GamebaseBackend\Domain\Repositories\Exceptions\RepositoryStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Domain\Repositories\Exceptions\RepositoryStatementExecutionFailureException;
use Mvreisg\GamebaseBackend\Domain\Repositories\Exceptions\RepositoryUnexistantRegisterException;
use Mvreisg\GamebaseBackend\Domain\Repositories\GamePlatformRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\GameRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\PlatformRepositoryInterface;

class GamePlatformService
{
    private GameRepositoryInterface $gameRepository;
    private PlatformRepositoryInterface $platformRepository;
    private GamePlatformRepositoryInterface $gamePlatformRepository;

    public function __construct(
        GameRepositoryInterface $gameRepository,
        PlatformRepositoryInterface $platformRepository,
        GamePlatformRepositoryInterface $gamePlatformRepository
    ) {
        $this->gameRepository = $gameRepository;
        $this->platformRepository = $platformRepository;
        $this->gamePlatformRepository = $gamePlatformRepository;
    }

    public function insert(int $platformId, int $gameId): GamePlatform
    {
        try {
            $gamePlatform = new GamePlatform(
                null,
                $platformId,
                $gameId
            );

            $gamePlatform->validatePlatformId();
            $gamePlatform->validateGameId();

            try {
                $validatedGameId = $gamePlatform->getGameId();

                $this->gameRepository->checkIfExists($validatedGameId);
            } catch (RepositoryUnexistantRegisterException $e) {
                throw new GamePlatformServiceUnexistantGameException(
                    "Game platform service error: Game repository: {$e->getMessage()}",
                    $e
                );
            }

            try {
                $validatedPlatformId = $gamePlatform->getPlatformId();

                $this->platformRepository->checkIfExists($validatedPlatformId);
            } catch (RepositoryUnexistantRegisterException $e) {
                throw new GamePlatformServiceUnexistantPlatformException(
                    "Game platform service error: Platform repository: {$e->getMessage()}",
                    $e
                );
            }

            $insertedGamePlatform = $this->gamePlatformRepository->insert($gamePlatform);

            return $insertedGamePlatform;
        } catch (
            GamePlatformServiceUnexistantGameException |
            GamePlatformServiceUnexistantPlatformException
            $e
        ) {
            throw $e;
        } catch (GamePlatformInvalidGameIdException $e) {
            throw new GamePlatformServiceInvalidGameIdException(
                "Game platform service error: {$e->getMessage()}",
                $e
            );
        } catch (GamePlatformInvalidPlatformIdException $e) {
            throw new GamePlatformServiceInvalidPlatformIdException(
                "Game platform service error: {$e->getMessage()}",
                $e
            );
        } catch (
            RepositoryStatementCreationFailureException |
            RepositoryStatementExecutionFailureException |
            \Throwable
        $e) {
            throw $e;
        }
    }

    public function update(int $id, int $platformId, int $gameId): bool
    {
        try {
            $gamePlatform = new GamePlatform(
                $id,
                $gameId,
                $platformId
            );

            $gamePlatform->validateId();
            $gamePlatform->validateGameId();
            $gamePlatform->validatePlatformId();

            $validatedId = $gamePlatform->getId();

            $this->gamePlatformRepository->checkIfExists($validatedId);

            try {
                $validatedGameId = $gamePlatform->getGameId();

                $this->gameRepository->checkIfExists($validatedGameId);
            } catch (RepositoryUnexistantRegisterException $e) {
                throw new GamePlatformServiceUnexistantGameException(
                    "Game platform service error: Game repository: {$e->getMessage()}",
                    $e
                );
            }

            try {
                $validatedPlatformId = $gamePlatform->getPlatformId();

                $this->platformRepository->checkIfExists($validatedPlatformId);
            } catch (RepositoryUnexistantRegisterException $e) {
                throw new GamePlatformServiceUnexistantPlatformException(
                    "Game platform service error: Platform repository: {$e->getMessage()}",
                    $e
                );
            }

            $wasUpdated = $this->gamePlatformRepository->update($gamePlatform);

            return $wasUpdated;
        } catch (
            GamePlatformServiceUnexistantGameException |
            GamePlatformServiceUnexistantPlatformException
        $e) {
            throw $e;
        } catch (GamePlatformInvalidIdException $e) {
            throw new GamePlatformServiceInvalidIdException(
                "Game platform service error: {$e->getMessage()}",
                $e
            );
        } catch (GamePlatformInvalidGameIdException $e) {
            throw new GamePlatformServiceInvalidGameIdException(
                "Game platform service error: {$e->getMessage()}",
                $e
            );
        } catch (GamePlatformInvalidPlatformIdException $e) {
            throw new GamePlatformServiceInvalidPlatformIdException(
                "Game platform service error: {$e->getMessage()}",
                $e
            );
        } catch (RepositoryUnexistantRegisterException $e) {
            throw new GamePlatformServiceUnexistantGamePlatformException(
                "Game platform service error: {$e->getMessage()}",
                $e
            );
        } catch (
            RepositoryStatementCreationFailureException |
            RepositoryStatementExecutionFailureException |
            \Throwable
        $e) {
            throw $e;
        }
    }

    public function delete(int $id): bool
    {
        try {
            $gamePlatform = new GamePlatform(
                $id
            );

            $gamePlatform->validateId();

            $validatedId = $gamePlatform->getId();

            $this->gamePlatformRepository->checkIfExists($validatedId);

            $wasDeleted = $this->gamePlatformRepository->delete($gamePlatform);

            return $wasDeleted;
        } catch (GamePlatformInvalidIdException $e) {
            throw new GamePlatformServiceInvalidIdException(
                "Game platform service error: {$e->getMessage()}",
                $e
            );
        } catch (RepositoryUnexistantRegisterException $e) {
            throw new GamePlatformServiceUnexistantGamePlatformException(
                "Game platform service error: {$e->getMessage()}",
                $e
            );
        } catch (
            RepositoryStatementCreationFailureException |
            RepositoryStatementExecutionFailureException |
            \Throwable
        $e) {
            throw $e;
        }
    }

    public function findById(int $id): GamePlatform
    {
        try {
            $gamePlatform = new GamePlatform(
                $id
            );

            $gamePlatform->validateId();

            $validatedId = $gamePlatform->getId();

            $fetchedGamePlatform = $this->gamePlatformRepository->findById(
                $validatedId
            );

            return $fetchedGamePlatform;
        } catch (GamePlatformInvalidIdException $e) {
            throw new GamePlatformServiceInvalidIdException(
                "Game platform service error: {$e->getMessage()}",
                $e
            );
        } catch (RepositoryUnexistantRegisterException $e) {
            throw new GamePlatformServiceUnexistantGamePlatformException(
                "Game platform service error: {$e->getMessage()}",
                $e
            );
        } catch (
            RepositoryStatementCreationFailureException |
            RepositoryStatementExecutionFailureException |
            \Throwable

        $e) {
            throw $e;
        }
    }

    public function findAll(): array
    {
        try {
            return $this->gamePlatformRepository->findAll();
        } catch (
            RepositoryStatementCreationFailureException |
            RepositoryStatementExecutionFailureException |
            \Throwable

        $e) {
            throw $e;
        }
    }
}
