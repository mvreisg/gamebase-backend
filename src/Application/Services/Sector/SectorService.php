<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services\Sector;

use Mvreisg\GamebaseBackend\Application\Services\Authentication\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\Authorization\AuthorizationService;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Data\Encoded\EncodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Authorization\Types\Permission\PermissionTypes;
use Mvreisg\GamebaseBackend\Domain\Authorization\Types\Sector\SectorTypes;
use Mvreisg\GamebaseBackend\Domain\Entities\Id;
use Mvreisg\GamebaseBackend\Domain\Entities\Sector;
use Mvreisg\GamebaseBackend\Domain\Entities\SectorCollection;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\SectorRepositoryInterface;

class SectorService
{
    private SectorRepositoryInterface $repository;
    private AuthenticationService $authenticationService;
    private AuthorizationService $authorizationService;

    public function __construct(
        SectorRepositoryInterface $repository,
        AuthenticationService $authenticationService,
        AuthorizationService $authorizationService,
    ) {
        $this->repository = $repository;
        $this->authenticationService = $authenticationService;
        $this->authorizationService = $authorizationService;
    }

    public function insert(Sector $sector, EncodedAuthenticationToken $token): Sector
    {
        try {
            $decodedToken = $this->authenticationService->validate(
                $token
            );

            $this->authorizationService->check(
                $decodedToken->getUserId(),
                SectorTypes::Sector,
                PermissionTypes::Create
            );

            $this->repository->checkDuplicatedNames(
                $sector->getName()
            );

            $insertedSector = $this->repository->insert($sector);

            return $insertedSector;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function update(Sector $sector, EncodedAuthenticationToken $token): bool
    {
        try {
            $decodedToken = $this->authenticationService->validate(
                $token
            );

            $this->authorizationService->check(
                $decodedToken->getUserId(),
                SectorTypes::Sector,
                PermissionTypes::Update
            );

            $this->repository->checkIfExists(
                $sector->getId()
            );

            $this->repository->checkDuplicatedNames(
                $sector->getName()
            );

            $wasUpdated = $this->repository->update($sector);

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
                SectorTypes::Sector,
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

    public function findById(Id $id, EncodedAuthenticationToken $token): Sector
    {
        try {
            $decodedToken = $this->authenticationService->validate(
                $token
            );

            $this->authorizationService->check(
                $decodedToken->getUserId(),
                SectorTypes::Sector,
                PermissionTypes::List
            );

            $fetchedSector = $this->repository->findById($id);

            return $fetchedSector;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAll(EncodedAuthenticationToken $token): SectorCollection
    {
        try {
            $decodedToken = $this->authenticationService->validate(
                $token
            );

            $this->authorizationService->check(
                $decodedToken->getUserId(),
                SectorTypes::Sector,
                PermissionTypes::List
            );

            return $this->repository->findAll();
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
