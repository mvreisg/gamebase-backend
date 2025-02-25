<?php

namespace Mvreisg\GamebaseBackend\Domain\Repositories;

use Mvreisg\GamebaseBackend\Domain\Entities\User;

interface UserRepositoryInterface
{
    public function insert(User $user): User;

    public function update(User $user): bool;

    public function setIsActive(int $id, bool $isActive): bool;

    public function findById(int $id): User|null;

    public function findAll(): array;

    public function hasDuplicatedUserName(string $name): bool;
}
