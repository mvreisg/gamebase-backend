<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\User\Repository;

use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\User\Entity\Collection\UserCollection;
use Mvreisg\GamebaseBackend\Domain\User\Entity\User;
use Mvreisg\GamebaseBackend\Domain\User\Repository\Dto\UserRepositoryInterfaceInsertDto;
use Mvreisg\GamebaseBackend\Domain\User\Repository\Dto\UserRepositoryInterfaceUpdateDto;
use Mvreisg\GamebaseBackend\Domain\User\ValueObject\Username\Username;

interface UserRepositoryInterface
{
    public function insert(UserRepositoryInterfaceInsertDto $dto): User;

    public function update(UserRepositoryInterfaceUpdateDto $dto): bool;

    public function setIsActive(Id $id, bool $isActive): bool;

    public function findById(Id $id): ?User;

    public function findByUsername(Username $username): ?User;

    public function findAll(): ?UserCollection;

    public function checkIfExists(Id $id): bool;

    public function checkDuplicatedUsernames(?Id $id, Username $username): bool;
}
