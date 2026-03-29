<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services\Game;

use Mvreisg\GamebaseBackend\Application\Services\Authentication\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\Authorization\AuthorizationService;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Data\Encoded\EncodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Authorization\Types\Permission\PermissionTypes;
use Mvreisg\GamebaseBackend\Domain\Authorization\Types\Sector\SectorTypes;
use Mvreisg\GamebaseBackend\Domain\Entities\Game;
use Mvreisg\GamebaseBackend\Domain\Entities\GameCollection;
use Mvreisg\GamebaseBackend\Domain\Entities\Id;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\GameRepositoryInterface;

class GameService
{
    private GameRepositoryInterface $repository;
    private AuthenticationService $authenticationService;
    private AuthorizationService $authorizationService;

    public function __construct(
        GameRepositoryInterface $repository,
        AuthenticationService $authenticationService,
        AuthorizationService $authorizationService
    ) {
        $this->repository = $repository;
        $this->authenticationService = $authenticationService;
        $this->authorizationService = $authorizationService;
    }

    public function insert(Game $game, EncodedAuthenticationToken $token): Game
    {
        try {
            $decodedToken = $this->authenticationService->validate(
                $token
            );

            $this->authorizationService->check(
                $decodedToken->getUserId(),
                SectorTypes::Game,
                PermissionTypes::Create
            );

            $this->repository->checkDuplicatedNames(
                $game->getName()
            );

            $insertedGame = $this->repository->insert($game);

            return $insertedGame;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function update(Game $game, EncodedAuthenticationToken $token): bool
    {
        try {
            $decodedToken = $this->authenticationService->validate(
                $token
            );

            $this->authorizationService->check(
                $decodedToken->getUserId(),
                SectorTypes::Game,
                PermissionTypes::Update
            );

            $this->repository->checkIfExists(
                $game->getId()
            );

            $this->repository->checkDuplicatedNames(
                $game->getName()
            );

            $wasUpdated = $this->repository->update($game);

            return $wasUpdated;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function setIsActive(Id $id, bool $isActive, EncodedAuthenticationToken $token): bool
    {
        try {
            $decodedToken = $this->authenticationService->validate(
                $token
            );

            $this->authorizationService->check(
                $decodedToken->getUserId(),
                SectorTypes::Game,
                PermissionTypes::Activate
            );

            $this->repository->checkIfExists($id);

            $wasUpdated = $this->repository->setIsActive(
                $id,
                $isActive
            );

            return $wasUpdated;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findById(Id $id, EncodedAuthenticationToken $token): Game
    {
        try {
            $decodedToken = $this->authenticationService->validate(
                $token
            );

            $this->authorizationService->check(
                $decodedToken->getUserId(),
                SectorTypes::Game,
                PermissionTypes::List
            );

            $foundGame = $this->repository->findById(
                $id
            );

            return $foundGame;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAll(EncodedAuthenticationToken $token): GameCollection
    {
        try {
            $decodedToken = $this->authenticationService->validate(
                $token
            );

            $this->authorizationService->check(
                $decodedToken->getUserId(),
                SectorTypes::Game,
                PermissionTypes::List
            );

            return $this->repository->findAll();
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
