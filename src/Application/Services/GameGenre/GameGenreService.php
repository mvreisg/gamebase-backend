<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services\GameGenre;

use Mvreisg\GamebaseBackend\Application\Services\Authentication\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\Authorization\AuthorizationService;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Data\Encoded\EncodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Authorization\Types\Permission\PermissionTypes;
use Mvreisg\GamebaseBackend\Domain\Authorization\Types\Sector\SectorTypes;
use Mvreisg\GamebaseBackend\Domain\Entities\GameGenre;
use Mvreisg\GamebaseBackend\Domain\Entities\GameGenreCollection;
use Mvreisg\GamebaseBackend\Domain\Entities\Id;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\GameGenreRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\GameRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\GenreRepositoryInterface;

class GameGenreService
{
    private GameGenreRepositoryInterface $gameGenreRepository;
    private GameRepositoryInterface $gameRepository;
    private GenreRepositoryInterface $genreRepository;
    private AuthenticationService $authenticationService;
    private AuthorizationService $authorizationService;

    public function __construct(
        GameGenreRepositoryInterface $gameGenreRepository,
        GameRepositoryInterface $gameRepository,
        GenreRepositoryInterface $genreRepository,
        AuthenticationService $authenticationService,
        AuthorizationService $authorizationService
    ) {
        $this->gameGenreRepository = $gameGenreRepository;
        $this->gameRepository = $gameRepository;
        $this->genreRepository = $genreRepository;
        $this->authenticationService = $authenticationService;
        $this->authorizationService = $authorizationService;
    }

    public function insert(GameGenre $gameGenre, EncodedAuthenticationToken $token): GameGenre
    {
        try {
            $decodedToken = $this->authenticationService->decode(
                $token
            );

            $this->authorizationService->check(
                $decodedToken->getUserId(),
                SectorTypes::GameGenre,
                PermissionTypes::Create
            );

            $this->gameRepository->checkIfExists(
                $gameGenre->getGameId()
            );

            $this->genreRepository->checkIfExists(
                $gameGenre->getGenreId()
            );

            $insertedGameGenre = $this->gameGenreRepository->insert($gameGenre);

            return $insertedGameGenre;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function update(GameGenre $gameGenre, EncodedAuthenticationToken $token): bool
    {
        try {
            $decodedToken = $this->authenticationService->decode(
                $token
            );

            $this->authorizationService->check(
                $decodedToken->getUserId(),
                SectorTypes::GameGenre,
                PermissionTypes::Update
            );

            $this->gameGenreRepository->checkIfExists(
                $gameGenre->getId()
            );

            $this->gameRepository->checkIfExists(
                $gameGenre->getGameId()
            );

            $this->genreRepository->checkIfExists(
                $gameGenre->getGenreId()
            );

            $wasUpdated = $this->gameGenreRepository->update($gameGenre);

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
                SectorTypes::GameGenre,
                PermissionTypes::Delete
            );

            $this->gameGenreRepository->checkIfExists($id);

            $wasDeleted = $this->gameGenreRepository->delete($id);

            return $wasDeleted;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findById(Id $id, EncodedAuthenticationToken $token): GameGenre
    {
        try {
            $decodedToken = $this->authenticationService->decode(
                $token
            );

            $this->authorizationService->check(
                $decodedToken->getUserId(),
                SectorTypes::GameGenre,
                PermissionTypes::List
            );

            $fetchedGameGenre = $this->gameGenreRepository->findById(
                $id
            );

            return $fetchedGameGenre;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAll(EncodedAuthenticationToken $token): GameGenreCollection
    {
        try {
            $decodedToken = $this->authenticationService->decode(
                $token
            );

            $this->authorizationService->check(
                $decodedToken->getUserId(),
                SectorTypes::GameGenre,
                PermissionTypes::List
            );

            return $this->gameGenreRepository->findAll();
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
