<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services\GamePlatform;

use Mvreisg\GamebaseBackend\Application\Services\Authentication\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\Authorization\AuthorizationService;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Data\Encoded\EncodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Authorization\Types\Permission\PermissionTypes;
use Mvreisg\GamebaseBackend\Domain\Authorization\Types\Sector\SectorTypes;
use Mvreisg\GamebaseBackend\Domain\Entities\GamePlatform;
use Mvreisg\GamebaseBackend\Domain\Entities\GamePlatformCollection;
use Mvreisg\GamebaseBackend\Domain\Entities\Id;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\GamePlatformRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\GameRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\PlatformRepositoryInterface;

class GamePlatformService
{
    private GameRepositoryInterface $gameRepository;
    private PlatformRepositoryInterface $platformRepository;
    private GamePlatformRepositoryInterface $gamePlatformRepository;
    private AuthenticationService $authenticationService;
    private AuthorizationService $authorizationService;

    public function __construct(
        GameRepositoryInterface $gameRepository,
        PlatformRepositoryInterface $platformRepository,
        GamePlatformRepositoryInterface $gamePlatformRepository,
        AuthenticationService $authenticationService,
        AuthorizationService $authorizationService
    ) {
        $this->gameRepository = $gameRepository;
        $this->platformRepository = $platformRepository;
        $this->gamePlatformRepository = $gamePlatformRepository;
        $this->authenticationService = $authenticationService;
        $this->authorizationService = $authorizationService;
    }

    public function insert(GamePlatform $gamePlatform, EncodedAuthenticationToken $token): GamePlatform
    {
        try {
            $decodedToken = $this->authenticationService->decode(
                $token
            );

            $this->authorizationService->check(
                $decodedToken->getUserId(),
                SectorTypes::GamePlatform,
                PermissionTypes::Create
            );

            $this->gameRepository->checkIfExists(
                $gamePlatform->getGameId()
            );

            $this->platformRepository->checkIfExists(
                $gamePlatform->getPlatformId()
            );

            $insertedGamePlatform = $this->gamePlatformRepository->insert($gamePlatform);

            return $insertedGamePlatform;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function update(GamePlatform $gamePlatform, EncodedAuthenticationToken $token): bool
    {
        try {
            $decodedToken = $this->authenticationService->decode(
                $token
            );

            $this->authorizationService->check(
                $decodedToken->getUserId(),
                SectorTypes::GamePlatform,
                PermissionTypes::Update
            );

            $this->gamePlatformRepository->checkIfExists(
                $gamePlatform->getId()
            );

            $this->gameRepository->checkIfExists(
                $gamePlatform->getGameId()
            );

            $this->platformRepository->checkIfExists(
                $gamePlatform->getPlatformId()
            );

            $wasUpdated = $this->gamePlatformRepository->update($gamePlatform);

            return $wasUpdated;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function delete(Id $id, EncodedAuthenticationToken $token): bool
    {
        try {
            $decodedToken = $this->authenticationService->decode(
                $token
            );

            $this->authorizationService->check(
                $decodedToken->getUserId(),
                SectorTypes::GamePlatform,
                PermissionTypes::Delete
            );

            $this->gamePlatformRepository->checkIfExists($id);

            $wasDeleted = $this->gamePlatformRepository->delete($id);

            return $wasDeleted;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findById(Id $id, EncodedAuthenticationToken $token): GamePlatform
    {
        try {
            $decodedToken = $this->authenticationService->decode(
                $token
            );

            $this->authorizationService->check(
                $decodedToken->getUserId(),
                SectorTypes::GamePlatform,
                PermissionTypes::List
            );

            $fetchedGamePlatform = $this->gamePlatformRepository->findById(
                $id
            );

            return $fetchedGamePlatform;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAll(EncodedAuthenticationToken $token): GamePlatformCollection
    {
        try {
            $decodedToken = $this->authenticationService->decode(
                $token
            );

            $this->authorizationService->check(
                $decodedToken->getUserId(),
                SectorTypes::GamePlatform,
                PermissionTypes::List
            );

            return $this->gamePlatformRepository->findAll();
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
