<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services;

use Mvreisg\GamebaseBackend\Application\Exceptions\Repositories\RepositoryException;
use Mvreisg\GamebaseBackend\Domain\Encryption\EncryptionInterface;
use Mvreisg\GamebaseBackend\Domain\Entities\UserEntity;
use Mvreisg\GamebaseBackend\Domain\Repositories\UserEntityRepositoryInterface;

class UserService
{
    private UserEntityRepositoryInterface $repository;
    private EncryptionInterface $encrypter;

    public function __construct(
        UserEntityRepositoryInterface $repository,
        EncryptionInterface $encrypter
    ) {
        $this->repository = $repository;
        $this->encrypter = $encrypter;
    }

    public function insert(string $userName, string $passWord, bool $isActive): UserEntity
    {
        $userEntity = new UserEntity(
            PHP_INT_MAX,
            $userName,
            $passWord,
            $isActive
        );

        try {
            $userEntity->validateUserName();
            $userEntity->validatePassWord();

            $validatedUserName = $userEntity->getUserName();

            $this->repository->checkDuplicatedUserNames($validatedUserName);

            $plainPassword = $userEntity->getPassWord();
            $encodedPassword = $this->encrypter->encrypt($plainPassword);
            $userEntity->setPassword($encodedPassword);
            $insertedUserEntity = $this->repository->insert($userEntity);

            return $insertedUserEntity;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function update(int $id, string $userName, string $passWord, bool $isActive): bool
    {
        $userEntity = new UserEntity(
            $id,
            $userName,
            $passWord,
            $isActive
        );

        try {
            $userEntity->validateId();
            $userEntity->validateUserName();
            $userEntity->validatePassWord();

            /*
            $validatedUserName = $user->getUserName();
            $hasDuplicatedNames = $this->repository->hasDuplicatedUserNames($validatedUserName);
            if ($hasDuplicatedNames) {
                throw new DatabaseDuplicatedEntryException(
                    'O nome do usuário a ser atualizado já existe no repositório!'
                );
            }*/

            $plainPassword = $userEntity->getPassWord();
            $encodedPassword = $this->encrypter->encrypt($plainPassword);
            $userEntity->setPassword($encodedPassword);
            $wasUpdated = $this->repository->update($userEntity);

            return $wasUpdated;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function setIsActive(int $id, bool $isActive): bool
    {
        $userEntity = new UserEntity(
            $id,
            '',
            '',
            $isActive
        );

        try {
            $userEntity->validateId();

            $validatedId = $userEntity->getId();
            $validatedIsActive = $userEntity->getIsActive();

            $wasUpdated = $this->repository->setIsActive($validatedId, $validatedIsActive);

            return $wasUpdated;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findById(int $id): UserEntity|null
    {
        $userEntity = new UserEntity(
            $id
        );

        try {
            $userEntity->validateId();

            $validatedId = $userEntity->getId();

            $fetchedUserEntity = $this->repository->findById($validatedId);

            return $fetchedUserEntity;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findByUserName(string $userName): UserEntity|null
    {
        $userEntity = new UserEntity(
            PHP_INT_MAX,
            $userName
        );

        try {
            $userEntity->validateUserName();

            $validatedUserName = $userEntity->getUserName();

            $fetchedUserEntity = $this->repository->findByUserName($validatedUserName);

            return $fetchedUserEntity;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAll(): array
    {
        try {
            $fetchedUserEntities = $this->repository->findAll();

            return $fetchedUserEntities;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
