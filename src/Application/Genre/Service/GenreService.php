<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Genre\Service;

use Mvreisg\GamebaseBackend\Application\Authorization\UseCase\CheckAuthorizationUseCase;
use Mvreisg\GamebaseBackend\Domain\Authorization\Permission\PermissionType;
use Mvreisg\GamebaseBackend\Domain\Authorization\Sector\SectorType;
use Mvreisg\GamebaseBackend\Domain\Genre\Entity\Collection\GenreCollection;
use Mvreisg\GamebaseBackend\Domain\Genre\Entity\Genre;
use Mvreisg\GamebaseBackend\Domain\Genre\Repository\GenreRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Genre\Service\GenreDomainService;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Psr\Log\LoggerInterface;

class GenreService
{
    private GenreRepositoryInterface $repository;
    private CheckAuthorizationUseCase $checkAuthorizationUseCase;
    private GenreDomainService $genreDomainService;
    private LoggerInterface $logger;

    public function __construct(
        GenreRepositoryInterface $repository,
        CheckAuthorizationUseCase $checkAuthorizationUseCase,
        GenreDomainService $genreDomainService,
        LoggerInterface $logger
    ) {
        $this->repository = $repository;
        $this->checkAuthorizationUseCase = $checkAuthorizationUseCase;
        $this->genreDomainService = $genreDomainService;
        $this->logger = $logger;
    }

    public function insert(Genre $genre, string $token): Genre
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::Genre,
                PermissionType::Create
            );

            $this->genreDomainService->ensureNameIsUnique(
                null,
                $genre->getName()
            );

            $insertedGenre = $this->repository->insert($genre);

            return $insertedGenre;
        } catch (\Throwable $e) {
            $this->logger->error("Error inserting genre", [
                "exception" => $e,
                "genre" => $genre
            ]);
            throw $e;
        }
    }

    public function update(Genre $genre, string $token): bool
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::Genre,
                PermissionType::Update
            );

            $this->genreDomainService->ensureGenreExists(
                $genre->getId()
            );

            $this->genreDomainService->ensureNameIsUnique(
                $genre->getId(),
                $genre->getName()
            );

            $wasUpdated = $this->repository->update($genre);

            return $wasUpdated;
        } catch (\Throwable $e) {
            $this->logger->error("Error updating genre", [
                "exception" => $e,
                "genre" => $genre
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

            return $wasUpdated;
        } catch (\Throwable $e) {
            $this->logger->error("Error setting genre active status", [
                "exception" => $e,
                "genreId" => $id,
                "isActive" => $isActive
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

            $fetchedGenreEntity = $this->repository->findById(
                $id
            );

            return $fetchedGenreEntity;
        } catch (\Throwable $e) {
            $this->logger->error("Error finding genre by id", [
                "exception" => $e,
                "genreId" => $id
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

            return $this->repository->findAll();
        } catch (\Throwable $e) {
            $this->logger->error("Error finding all genres", [
                "exception" => $e
            ]);
            throw $e;
        }
    }
}
