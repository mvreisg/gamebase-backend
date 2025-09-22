<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Repositories;

use Mvreisg\GamebaseBackend\Domain\Entities\UserEntity;

interface UserEntityRepositoryInterface
{
    public function insert(UserEntity $userEntity): UserEntity;

    public function update(UserEntity $userEntity): bool;

    public function setIsActive(int $id, bool $isActive): bool;

    public function findById(int $id): UserEntity;

    public function findByUserName(string $userName): UserEntity;

    public function findAll(): array;

    public function checkDuplicatedUserNames(string $name): void;
}
