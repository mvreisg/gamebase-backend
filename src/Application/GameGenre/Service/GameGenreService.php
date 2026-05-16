<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\GameGenre\Service;

use Mvreisg\GamebaseBackend\Application\Authorization\UseCase\CheckAuthorizationUseCase;
use Mvreisg\GamebaseBackend\Domain\Authorization\Permission\PermissionType;
use Mvreisg\GamebaseBackend\Domain\Authorization\Sector\SectorType;
use Mvreisg\GamebaseBackend\Domain\Game\Service\GameDomainService;
use Mvreisg\GamebaseBackend\Domain\GameGenre\Entity\Collection\GameGenreCollection;
use Mvreisg\GamebaseBackend\Domain\GameGenre\Entity\GameGenre;
use Mvreisg\GamebaseBackend\Domain\GameGenre\Repository\GameGenreRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\GameGenre\Service\GameGenreDomainService;
use Mvreisg\GamebaseBackend\Domain\Genre\Service\GenreDomainService;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;

class GameGenreService
{
    private CheckAuthorizationUseCase $checkAuthorizationUseCase;
    private GameDomainService $gameDomainService;
    private GenreDomainService $genreDomainService;
    private GameGenreDomainService $gameGenreDomainService;
    private GameGenreRepositoryInterface $repository;

    public function __construct(
        CheckAuthorizationUseCase $checkAuthorizationUseCase,
        GameDomainService $gameDomainService,
        GenreDomainService $genreDomainService,
        GameGenreDomainService $gameGenreDomainService,
        GameGenreRepositoryInterface $repository
    ) {
        $this->checkAuthorizationUseCase = $checkAuthorizationUseCase;
        $this->gameDomainService = $gameDomainService;
        $this->genreDomainService = $genreDomainService;
        $this->gameGenreDomainService = $gameGenreDomainService;
        $this->repository = $repository;
    }

    public function insert(GameGenre $gameGenre, string $token): GameGenre
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::GameGenre,
                PermissionType::Create
            );

            $this->gameDomainService->ensureGameExists(
                $gameGenre->getGame()->getId()
            );

            $this->genreDomainService->ensureGenreExists(
                $gameGenre->getGenre()->getId()
            );

            $insertedGameGenre = $this->repository->insert($gameGenre);

            return $insertedGameGenre;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function update(GameGenre $gameGenre, string $token): bool
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::GameGenre,
                PermissionType::Update
            );

            $this->gameGenreDomainService->ensureGameGenreExists(
                $gameGenre->getId()
            );

            $this->gameDomainService->ensureGameExists(
                $gameGenre->getGame()->getId()
            );

            $this->genreDomainService->ensureGenreExists(
                $gameGenre->getGenre()->getId()
            );

            $wasUpdated = $this->repository->update($gameGenre);

            return $wasUpdated;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function delete(Id $id, string $token): bool
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::GameGenre,
                PermissionType::Delete
            );

            $this->gameGenreDomainService->ensureGameGenreExists(
                $id
            );

            $wasDeleted = $this->repository->delete($id);

            return $wasDeleted;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findById(Id $id, string $token): ?GameGenre
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::GameGenre,
                PermissionType::List
            );

            $fetchedGameGenre = $this->repository->findById(
                $id
            );

            return $fetchedGameGenre;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAll(string $token): ?GameGenreCollection
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::GameGenre,
                PermissionType::List
            );

            return $this->repository->findAll();
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
