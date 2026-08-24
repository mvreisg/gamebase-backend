<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\GameGenre\Service;

use Mvreisg\GamebaseBackend\Application\Authorization\UseCase\CheckAuthorizationUseCase;
use Mvreisg\GamebaseBackend\Application\GameGenre\Service\Dto\GameGenreServiceInsertDto;
use Mvreisg\GamebaseBackend\Application\GameGenre\Service\Dto\GameGenreServiceUpdateDto;
use Mvreisg\GamebaseBackend\Domain\Authorization\Permission\PermissionType;
use Mvreisg\GamebaseBackend\Domain\Authorization\Sector\SectorType;
use Mvreisg\GamebaseBackend\Domain\Game\Service\GameDomainService;
use Mvreisg\GamebaseBackend\Domain\GameGenre\Entity\Collection\GameGenreCollection;
use Mvreisg\GamebaseBackend\Domain\GameGenre\Entity\GameGenre;
use Mvreisg\GamebaseBackend\Domain\GameGenre\Repository\Dto\GameGenreRepositoryInterfaceInsertDto;
use Mvreisg\GamebaseBackend\Domain\GameGenre\Repository\Dto\GameGenreRepositoryInterfaceUpdateDto;
use Mvreisg\GamebaseBackend\Domain\GameGenre\Repository\GameGenreRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\GameGenre\Service\GameGenreDomainService;
use Mvreisg\GamebaseBackend\Domain\Genre\Service\GenreDomainService;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Psr\Log\LoggerInterface;

class GameGenreService
{
    private CheckAuthorizationUseCase $checkAuthorizationUseCase;
    private GameDomainService $gameDomainService;
    private GenreDomainService $genreDomainService;
    private GameGenreDomainService $gameGenreDomainService;
    private GameGenreRepositoryInterface $repository;
    private LoggerInterface $logger;

    public function __construct(
        CheckAuthorizationUseCase $checkAuthorizationUseCase,
        GameDomainService $gameDomainService,
        GenreDomainService $genreDomainService,
        GameGenreDomainService $gameGenreDomainService,
        GameGenreRepositoryInterface $repository,
        LoggerInterface $logger
    ) {
        $this->checkAuthorizationUseCase = $checkAuthorizationUseCase;
        $this->gameDomainService = $gameDomainService;
        $this->genreDomainService = $genreDomainService;
        $this->gameGenreDomainService = $gameGenreDomainService;
        $this->repository = $repository;
        $this->logger = $logger;
    }

    public function insert(GameGenreServiceInsertDto $dto, string $token): GameGenre
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::GameGenre,
                PermissionType::Create
            );

            $this->gameDomainService->ensureGameExists(
                $dto->gameId
            );

            $this->genreDomainService->ensureGenreExists(
                $dto->genreId
            );

            $insertedGameGenre = $this->repository->insert(
                new GameGenreRepositoryInterfaceInsertDto(
                    $dto->gameId,
                    $dto->genreId
                )
            );

            return $insertedGameGenre;
        } catch (\Throwable $e) {
            $this->logger->error("Error inserting GameGenre", [
                "exception" => $e,
                "dto" => $dto,
            ]);
            throw $e;
        }
    }

    public function update(GameGenreServiceUpdateDto $dto, string $token): bool
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::GameGenre,
                PermissionType::Update
            );

            $this->gameGenreDomainService->ensureGameGenreExists(
                $dto->id
            );

            $this->gameDomainService->ensureGameExists(
                $dto->gameId
            );

            $this->genreDomainService->ensureGenreExists(
                $dto->genreId
            );

            $wasUpdated = $this->repository->update(
                new GameGenreRepositoryInterfaceUpdateDto(
                    $dto->id,
                    $dto->gameId,
                    $dto->genreId
                )
            );

            return $wasUpdated;
        } catch (\Throwable $e) {
            $this->logger->error("Error updating GameGenre", [
                "exception" => $e,
                "dto" => $dto,
            ]);
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
            $this->logger->error("Error deleting GameGenre", [
                "exception" => $e,
                "gameGenreId" => $id,
            ]);
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
            $this->logger->error("Error fetching GameGenre by ID", [
                "exception" => $e,
                "gameGenreId" => $id,
            ]);
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
            $this->logger->error("Error fetching all GameGenres", [
                "exception" => $e,
            ]);
            throw $e;
        }
    }
}
