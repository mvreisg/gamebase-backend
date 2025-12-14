<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Repositories;

use Mvreisg\GamebaseBackend\Domain\Entities\User\User;

interface UserRepositoryInterface
{
    public function insert(User $user): User;

    public function update(User $user): bool;

    public function setIsActive(int $id, bool $isActive): bool;

    public function findById(int $id): User;

    public function findByUsername(string $username): User;

    public function findAll(): array;

    public function checkIfExists(int $id): void;

    public function checkDuplicatedUserNames(string $name): void;
}
