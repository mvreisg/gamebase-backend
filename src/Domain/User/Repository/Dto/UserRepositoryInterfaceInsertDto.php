<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\User\Repository\Dto;

use Mvreisg\GamebaseBackend\Domain\User\ValueObject\Password\Password;
use Mvreisg\GamebaseBackend\Domain\User\ValueObject\Username\Username;

final readonly class UserRepositoryInterfaceInsertDto
{
    public function __construct(
        public Username $username,
        public Password $password,
        public bool $isActive
    ) {
    }
}
