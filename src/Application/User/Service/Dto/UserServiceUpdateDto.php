<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\User\Service\Dto;

use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\User\ValueObject\Password\Password;
use Mvreisg\GamebaseBackend\Domain\User\ValueObject\Username\Username;

final readonly class UserServiceUpdateDto
{
    public function __construct(
        public Id $id,
        public Username $username,
        public Password $password,
        public bool $isActive
    ) {
    }
}
