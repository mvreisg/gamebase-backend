<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services\Platform;

use Mvreisg\GamebaseBackend\Application\Services\Authentication\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\Authorization\AuthorizationService;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Data\Encoded\EncodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Authorization\Types\Permission\PermissionTypes;
use Mvreisg\GamebaseBackend\Domain\Authorization\Types\Sector\SectorTypes;
use Mvreisg\GamebaseBackend\Domain\Entities\Id;
use Mvreisg\GamebaseBackend\Domain\Entities\Platform;
use Mvreisg\GamebaseBackend\Domain\Entities\PlatformCollection;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\PlatformRepositoryInterface;

class PlatformService
{
    private PlatformRepositoryInterface $repository;
    private AuthenticationService $authenticationService;
    private AuthorizationService $authorizationService;

    public function __construct(
        PlatformRepositoryInterface $repository,
        AuthenticationService $authenticationService,
        AuthorizationService $authorizationService
    ) {
        $this->repository = $repository;
        $this->authenticationService = $authenticationService;
        $this->authorizationService = $authorizationService;
    }

    public function insert(Platform $platform, EncodedAuthenticationToken $token): Platform
    {
        try {
            $decodedToken = $this->authenticationService->decode(
                $token
            );

            $this->authorizationService->check(
                $decodedToken->getUserId(),
                SectorTypes::Platform,
                PermissionTypes::Create
            );

            $this->repository->checkDuplicatedNames(
                $platform->getName()
            );

            $insertedPlatform = $this->repository->insert($platform);

            return $insertedPlatform;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function update(Platform $platform, EncodedAuthenticationToken $token): bool
    {
        try {
            $decodedToken = $this->authenticationService->decode(
                $token
            );

            $this->authorizationService->check(
                $decodedToken->getUserId(),
                SectorTypes::Platform,
                PermissionTypes::Update
            );

            $this->repository->checkIfExists(
                $platform->getId()
            );

            $this->repository->checkDuplicatedNames(
                $platform->getName()
            );

            $wasUpdated = $this->repository->update($platform);

            return $wasUpdated;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function setIsActive(Id $id, bool $isActive, EncodedAuthenticationToken $token): bool
    {
        try {
            $decodedToken = $this->authenticationService->decode(
                $token
            );

            $this->authorizationService->check(
                $decodedToken->getUserId(),
                SectorTypes::Platform,
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

    public function findById(Id $id, EncodedAuthenticationToken $token): Platform
    {
        try {
            $decodedToken = $this->authenticationService->decode(
                $token
            );

            $this->authorizationService->check(
                $decodedToken->getUserId(),
                SectorTypes::Platform,
                PermissionTypes::List
            );

            $fetchedPlatform = $this->repository->findById($id);

            return $fetchedPlatform;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAll(EncodedAuthenticationToken $token): PlatformCollection
    {
        try {
            $decodedToken = $this->authenticationService->decode(
                $token
            );

            $this->authorizationService->check(
                $decodedToken->getUserId(),
                SectorTypes::Platform,
                PermissionTypes::List
            );

            return $this->repository->findAll();
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
