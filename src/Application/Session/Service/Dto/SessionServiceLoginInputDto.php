<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Session\Service\Dto;

use Mvreisg\GamebaseBackend\Domain\User\ValueObject\Password\Password;
use Mvreisg\GamebaseBackend\Domain\User\ValueObject\Username\Username;

final readonly class SessionServiceLoginInputDto
{
    public function __construct(
        public Username $username,
        public Password $password,
        public bool $oneWeekLogin
    ) {
    }
}
