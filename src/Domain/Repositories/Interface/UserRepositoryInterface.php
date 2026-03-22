<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Repositories\Interface;

use Mvreisg\GamebaseBackend\Domain\Entities\Id;
use Mvreisg\GamebaseBackend\Domain\Entities\User;
use Mvreisg\GamebaseBackend\Domain\Entities\UserCollection;
use Mvreisg\GamebaseBackend\Domain\Entities\Username;

interface UserRepositoryInterface
{
    public function insert(User $user): User;

    public function update(User $user): bool;

    public function setIsActive(Id $id, bool $isActive): bool;

    public function findById(Id $id): User;

    public function findByUsername(Username $username): User;

    public function findAll(): UserCollection;

    public function checkIfExists(Id $id): void;

    public function checkDuplicatedUsernames(Username $username): void;
}
