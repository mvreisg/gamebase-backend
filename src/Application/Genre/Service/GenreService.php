<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Genre\Service;

use Mvreisg\GamebaseBackend\Application\Authorization\UseCase\CheckAuthorizationUseCase;
use Mvreisg\GamebaseBackend\Application\Genre\Service\Dto\GenreServiceInsertDto;
use Mvreisg\GamebaseBackend\Application\Genre\Service\Dto\GenreServiceUpdateDto;
use Mvreisg\GamebaseBackend\Domain\Authorization\Permission\PermissionType;
use Mvreisg\GamebaseBackend\Domain\Authorization\Sector\SectorType;
use Mvreisg\GamebaseBackend\Domain\Genre\Entity\Collection\GenreCollection;
use Mvreisg\GamebaseBackend\Domain\Genre\Entity\Genre;
use Mvreisg\GamebaseBackend\Domain\Genre\Repository\Dto\GenreRepositoryInterfaceInsertDto;
use Mvreisg\GamebaseBackend\Domain\Genre\Repository\Dto\GenreRepositoryInterfaceUpdateDto;
use Mvreisg\GamebaseBackend\Domain\Genre\Repository\GenreRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Genre\Service\GenreDomainService;
use Mvreisg\GamebaseBackend\Domain\Shared\Interface\ClockInterface;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Psr\Log\LoggerInterface;

class GenreService
{
    private GenreRepositoryInterface $repository;
    private CheckAuthorizationUseCase $checkAuthorizationUseCase;
    private GenreDomainService $genreDomainService;
    private ClockInterface $clock;
    private LoggerInterface $logger;

    public function __construct(
        GenreRepositoryInterface $repository,
        CheckAuthorizationUseCase $checkAuthorizationUseCase,
        GenreDomainService $genreDomainService,
        ClockInterface $clock,
        LoggerInterface $logger
    ) {
        $this->repository = $repository;
        $this->checkAuthorizationUseCase = $checkAuthorizationUseCase;
        $this->genreDomainService = $genreDomainService;
        $this->clock = $clock;
        $this->logger = $logger;
    }

    public function insert(GenreServiceInsertDto $dto, string $token): Genre
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::Genre,
                PermissionType::Create
            );

            $this->genreDomainService->ensureNameIsUniqueOnInsert(
                $dto->name
            );

            $insertedGenre = $this->repository->insert(
                new GenreRepositoryInterfaceInsertDto(
                    $dto->name,
                    $dto->isActive
                )
            );

            $this->logger->notice("Genre inserted succesfully!", [
                "id" => $insertedGenre->getId()->getValue(),
                "name" => $insertedGenre->getName()->getValue(),
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);

            return $insertedGenre;
        } catch (\Throwable $e) {
            $this->logger->error("Error inserting Genre!", [
                "exception" => $e->getMessage(),
                "name" => $dto->name->getValue(),
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);
            throw $e;
        }
    }

    public function update(GenreServiceUpdateDto $dto, string $token): bool
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::Genre,
                PermissionType::Update
            );

            $this->genreDomainService->ensureGenreExists(
                $dto->id
            );

            $this->genreDomainService->ensureNameIsUniqueOnUpdate(
                $dto->name,
                $dto->id
            );

            $wasUpdated = $this->repository->update(
                new GenreRepositoryInterfaceUpdateDto(
                    $dto->id,
                    $dto->name,
                    $dto->isActive
                )
            );

            $this->logger->notice("Genre data updated succesfully!", [
                "wasUpdated" => $wasUpdated,
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);

            return $wasUpdated;
        } catch (\Throwable $e) {
            $this->logger->error("Error updating Genre!", [
                "exception" => $e->getMessage(),
                "name" => $dto->name->getValue(),
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);
            throw $e;
        }
    }

    public function setIsActive(Id $id, bool $isActive, string $token): bool
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::Genre,
                PermissionType::Activate
            );

            $this->genreDomainService->ensureGenreExists(
                $id
            );

            $wasUpdated = $this->repository->setIsActive(
                $id,
                $isActive
            );

            $this->logger->notice("Genre isActive updated succesfully!", [
                "wasUpdated" => $wasUpdated,
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);

            return $wasUpdated;
        } catch (\Throwable $e) {
            $this->logger->error("Error setting Genre active status!", [
                "exception" => $e->getMessage(),
                "id" => $id->getValue(),
                "isActive" => $isActive,
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);
            throw $e;
        }
    }

    public function findById(Id $id, string $token): ?Genre
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::Genre,
                PermissionType::List
            );

            $fetchedGenre = $this->repository->findById(
                $id
            );

            $this->logger->notice("Genre found by id succesfully!", [
                "id" => $fetchedGenre->getId()->getValue(),
                "name" => $fetchedGenre->getName()->getValue(),
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);

            return $fetchedGenre;
        } catch (\Throwable $e) {
            $this->logger->error("Error finding Genre by id!", [
                "exception" => $e->getMessage(),
                "id" => $id->getValue(),
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);
            throw $e;
        }
    }

    public function findAll(string $token): ?GenreCollection
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::Genre,
                PermissionType::List
            );

            $genres = $this->repository->findAll();

            $this->logger->notice("All Genres found succesfully!", [
                "count" => $genres->count(),
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);

            return $genres;
        } catch (\Throwable $e) {
            $this->logger->error("Error finding all Genres!", [
                "exception" => $e->getMessage(),
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);
            throw $e;
        }
    }
}
