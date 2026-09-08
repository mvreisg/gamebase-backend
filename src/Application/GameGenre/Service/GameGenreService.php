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
use Mvreisg\GamebaseBackend\Domain\Permission\ValueObject\PermissionValue\PermissionValue;
use Mvreisg\GamebaseBackend\Domain\Sector\ValueObject\SectorValue\SectorValue;
use Mvreisg\GamebaseBackend\Domain\Shared\Interface\ClockInterface;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Psr\Log\LoggerInterface;

class GameGenreService
{
    private CheckAuthorizationUseCase $checkAuthorizationUseCase;
    private GameDomainService $gameDomainService;
    private GenreDomainService $genreDomainService;
    private GameGenreDomainService $gameGenreDomainService;
    private GameGenreRepositoryInterface $repository;
    private ClockInterface $clock;
    private LoggerInterface $logger;

    public function __construct(
        CheckAuthorizationUseCase $checkAuthorizationUseCase,
        GameDomainService $gameDomainService,
        GenreDomainService $genreDomainService,
        GameGenreDomainService $gameGenreDomainService,
        GameGenreRepositoryInterface $repository,
        ClockInterface $clock,
        LoggerInterface $logger
    ) {
        $this->checkAuthorizationUseCase = $checkAuthorizationUseCase;
        $this->gameDomainService = $gameDomainService;
        $this->genreDomainService = $genreDomainService;
        $this->gameGenreDomainService = $gameGenreDomainService;
        $this->repository = $repository;
        $this->clock = $clock;
        $this->logger = $logger;
    }

    public function insert(GameGenreServiceInsertDto $dto, string $token): GameGenre
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorValue::from(SectorType::GameGenre),
                PermissionValue::from(PermissionType::Create)
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

            $this->logger->notice("GameGenre inserted succesfully!", [
                "id" => $insertedGameGenre->getId()->getValue(),
                "game" => [
                    "id" => $insertedGameGenre->getGame()->getId()->getValue()
                ],
                "genre" => [
                    "id" => $insertedGameGenre->getGenre()->getId()->getValue()
                ],
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);

            return $insertedGameGenre;
        } catch (\Throwable $e) {
            $this->logger->error("Error inserting GameGenre!", [
                "exception" => $e->getMessage(),
                "game" => [
                    "id" => $dto->gameId->getValue()
                ],
                "genre" => [
                    "id" => $dto->genreId->getValue()
                ],
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);
            throw $e;
        }
    }

    public function update(GameGenreServiceUpdateDto $dto, string $token): bool
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorValue::from(SectorType::GameGenre),
                PermissionValue::from(PermissionType::Update)
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

            $this->logger->notice("GameGenre data updated succesfully!", [
                "id" => $dto->id->getValue(),
                "wasUpdated" => $wasUpdated,
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);

            return $wasUpdated;
        } catch (\Throwable $e) {
            $this->logger->error("Error updating GameGenre!", [
                "exception" => $e->getMessage(),
                "game" => [
                    "id" => $dto->gameId->getValue()
                ],
                "genre" => [
                    "id" => $dto->genreId->getValue()
                ],
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);
            throw $e;
        }
    }

    public function delete(Id $id, string $token): bool
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorValue::from(SectorType::GameGenre),
                PermissionValue::from(PermissionType::Delete)
            );

            $this->gameGenreDomainService->ensureGameGenreExists(
                $id
            );

            $wasDeleted = $this->repository->delete($id);

            $this->logger->notice("GameGenre deleted succesfully!", [
                "id" => $id->getValue(),
                "wasDeleted" => $wasDeleted,
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);

            return $wasDeleted;
        } catch (\Throwable $e) {
            $this->logger->error("Error deleting GameGenre!", [
                "exception" => $e->getMessage(),
                "id" => $id->getValue(),
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);
            throw $e;
        }
    }

    public function findById(Id $id, string $token): ?GameGenre
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorValue::from(SectorType::GameGenre),
                PermissionValue::from(PermissionType::List)
            );

            $fetchedGameGenre = $this->repository->findById(
                $id
            );

            if ($fetchedGameGenre === null) {
                $this->logger->notice("GameGenre not found!", [
                    "id" => $id->getValue(),
                    "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
                ]);
                return null;
            }

            $this->logger->notice("GameGenre found by id succesfully!", [
                "id" => $fetchedGameGenre->getId()->getValue(),
                "game" => [
                    "id" => $fetchedGameGenre->getGame()->getId()->getValue(),
                ],
                "genre" => [
                    "id" => $fetchedGameGenre->getGenre()->getId()->getValue(),
                ],
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);

            return $fetchedGameGenre;
        } catch (\Throwable $e) {
            $this->logger->error("Error finding GameGenre by id!", [
                "exception" => $e->getMessage(),
                "id" => $id->getValue(),
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);
            throw $e;
        }
    }

    public function findAll(string $token): ?GameGenreCollection
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorValue::from(SectorType::GameGenre),
                PermissionValue::from(PermissionType::List)
            );

            $gameGenres = $this->repository->findAll();

            if ($gameGenres === null) {
                $this->logger->notice("No GameGenres found!", [
                    "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
                ]);
                return null;
            }

            $this->logger->notice("All GameGenres found succesfully!", [
                "count" => $gameGenres->count(),
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);

            return $gameGenres;
        } catch (\Throwable $e) {
            $this->logger->error("Error fetching all GameGenres", [
                "exception" => $e->getMessage(),
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);
            throw $e;
        }
    }
}
