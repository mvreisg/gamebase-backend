<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\User\Service;

use Mvreisg\GamebaseBackend\Application\Authorization\UseCase\CheckAuthorizationUseCase;
use Mvreisg\GamebaseBackend\Application\User\Service\Dto\UserServiceInsertDto;
use Mvreisg\GamebaseBackend\Application\User\Service\Dto\UserServiceUpdateDto;
use Mvreisg\GamebaseBackend\Domain\Authorization\Permission\PermissionType;
use Mvreisg\GamebaseBackend\Domain\Authorization\Sector\SectorType;
use Mvreisg\GamebaseBackend\Domain\Encryption\Interface\EncryptionInterface;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\User\Entity\Collection\UserCollection;
use Mvreisg\GamebaseBackend\Domain\User\Entity\User;
use Mvreisg\GamebaseBackend\Domain\User\Repository\Dto\UserRepositoryInterfaceInsertDto;
use Mvreisg\GamebaseBackend\Domain\User\Repository\Dto\UserRepositoryInterfaceUpdateDto;
use Mvreisg\GamebaseBackend\Domain\User\Repository\UserRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\User\Service\UserDomainService;
use Mvreisg\GamebaseBackend\Domain\User\ValueObject\Password\Encoded\EncodedPassword;
use Mvreisg\GamebaseBackend\Domain\User\ValueObject\Username\Username;
use Psr\Log\LoggerInterface;

class UserService
{
    private UserRepositoryInterface $repository;
    private EncryptionInterface $encrypter;
    private CheckAuthorizationUseCase $checkAuthorizationUseCase;
    private UserDomainService $userDomainService;
    private LoggerInterface $logger;

    public function __construct(
        UserRepositoryInterface $repository,
        EncryptionInterface $encrypter,
        CheckAuthorizationUseCase $checkAuthorizationUseCase,
        UserDomainService $userDomainService,
        LoggerInterface $logger
    ) {
        $this->repository = $repository;
        $this->encrypter = $encrypter;
        $this->checkAuthorizationUseCase = $checkAuthorizationUseCase;
        $this->userDomainService = $userDomainService;
        $this->logger = $logger;
    }

    public function insert(UserServiceInsertDto $dto, string $token): User
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::User,
                PermissionType::Create
            );

            $this->userDomainService->ensureUsernameIsUnique(
                $dto->username
            );

            $encodedPassword = $this->encrypter->encrypt(
                $dto->password->getValue()
            );

            $insertedUser = $this->repository->insert(
                new UserRepositoryInterfaceInsertDto(
                    $dto->username,
                    EncodedPassword::create($encodedPassword),
                    $dto->isActive
                )
            );

            return $insertedUser;
        } catch (\Throwable $e) {
            $this->logger->error("Error inserting user", [
                "exception" => $e,
                "dto" => $dto
            ]);
            throw $e;
        }
    }

    public function update(UserServiceUpdateDto $dto, string $token): bool
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::User,
                PermissionType::Update
            );

            $this->userDomainService->ensureUserExists(
                $dto->id
            );

            $this->userDomainService->ensureUsernameIsUnique(
                $dto->username
            );

            $fetched = $this->repository->findById(
                $dto->id
            );

            $encodedPassword = $fetched->getPassword()->getValue();

            $decodedPassword = $this->encrypter->decrypt(
                $encodedPassword
            );

            $isHashEqual = strcmp(
                $dto->password->getValue(),
                $decodedPassword
            ) === 0;

            if ($isHashEqual === false) {
                $encodedPassword = $this->encrypter->encrypt(
                    $dto->password->getValue()
                );
            }

            $wasUpdated = $this->repository->update(
                new UserRepositoryInterfaceUpdateDto(
                    $dto->id,
                    $dto->username,
                    EncodedPassword::create($encodedPassword),
                    $dto->isActive
                )
            );

            return $wasUpdated;
        } catch (\Throwable $e) {
            $this->logger->error("Error updating user", [
                "exception" => $e,
                "dto" => $dto
            ]);
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
            $this->logger->error("Error setting user active status", [
                "exception" => $e,
                "userId" => $id,
                "isActive" => $isActive
            ]);
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
            $this->logger->error("Error finding user by ID", [
                "exception" => $e,
                "userId" => $id
            ]);
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
            $this->logger->error("Error finding user by username", [
                "exception" => $e,
                "username" => $username
            ]);
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
            $this->logger->error("Error finding all users", [
                "exception" => $e
            ]);
            throw $e;
        }
    }
}
