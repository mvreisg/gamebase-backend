<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\User\Service;

use Mvreisg\GamebaseBackend\Application\Authorization\UseCase\CheckAuthorizationUseCase;
use Mvreisg\GamebaseBackend\Domain\Authorization\Permission\PermissionType;
use Mvreisg\GamebaseBackend\Domain\Authorization\Sector\SectorType;
use Mvreisg\GamebaseBackend\Domain\Encryption\Interface\EncryptionInterface;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\User\Entity\Collection\UserCollection;
use Mvreisg\GamebaseBackend\Domain\User\Entity\User;
use Mvreisg\GamebaseBackend\Domain\User\Repository\UserRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\User\Service\UserDomainService;
use Mvreisg\GamebaseBackend\Domain\User\ValueObject\Password\Encoded\EncodedPassword;
use Mvreisg\GamebaseBackend\Domain\User\ValueObject\Username\Username;

class UserService
{
    private UserRepositoryInterface $repository;
    private EncryptionInterface $encrypter;
    private CheckAuthorizationUseCase $checkAuthorizationUseCase;
    private UserDomainService $userDomainService;

    public function __construct(
        UserRepositoryInterface $repository,
        EncryptionInterface $encrypter,
        CheckAuthorizationUseCase $checkAuthorizationUseCase,
        UserDomainService $userDomainService
    ) {
        $this->repository = $repository;
        $this->encrypter = $encrypter;
        $this->checkAuthorizationUseCase = $checkAuthorizationUseCase;
        $this->userDomainService = $userDomainService;
    }

    public function insert(User $new, string $token): User
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::User,
                PermissionType::Create
            );

            $this->userDomainService->ensureUsernameIsUnique(
                null,
                $new->getUsername()
            );

            $encodedPassword = $this->encrypter->encrypt(
                $new->getPassword()->getValue()
            );

            $insertedUser = $this->repository->insert(
                User::create(
                    null,
                    $new->getUsername(),
                    EncodedPassword::create($encodedPassword),
                    $new->getIsActive()
                )
            );

            return $insertedUser;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function update(User $existant, string $token): bool
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::User,
                PermissionType::Update
            );

            $this->userDomainService->ensureUserExists(
                $existant->getId()
            );

            $this->userDomainService->ensureUsernameIsUnique(
                $existant->getId(),
                $existant->getUsername()
            );

            $fetched = $this->repository->findById(
                $existant->getId()
            );

            $encodedPassword = $fetched->getPassword()->getValue();

            $decodedPassword = $this->encrypter->decrypt(
                $encodedPassword
            );

            $isHashEqual = strcmp(
                $existant->getPassword()->getValue(),
                $decodedPassword
            ) === 0;

            if ($isHashEqual === false) {
                $encodedPassword = $this->encrypter->encrypt(
                    $existant->getPassword()->getValue()
                );
            }

            $user = User::create(
                $existant->getId(),
                $existant->getUsername(),
                EncodedPassword::create($encodedPassword),
                $existant->getIsActive()
            );

            $wasUpdated = $this->repository->update(
                $user
            );

            return $wasUpdated;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function setIsActive(Id $id, bool $isActive, string $token): bool
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::User,
                PermissionType::Activate
            );

            $this->userDomainService->ensureUserExists(
                $id
            );

            $wasUpdated = $this->repository->setIsActive(
                $id,
                $isActive
            );

            return $wasUpdated;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findById(Id $id, string $token): ?User
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::User,
                PermissionType::List
            );

            $fetchedUser = $this->repository->findById($id);

            return $fetchedUser;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findByUsername(Username $username, string $token): ?User
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::User,
                PermissionType::List
            );

            $fetchedUser = $this->repository->findByUsername($username);

            return $fetchedUser;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAll(string $token): ?UserCollection
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::User,
                PermissionType::List
            );

            return $this->repository->findAll();
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
