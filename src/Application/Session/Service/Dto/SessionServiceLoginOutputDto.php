<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Session\Service\Dto;

use Mvreisg\GamebaseBackend\Domain\Session\Data\SessionData;

final readonly class SessionServiceLoginOutputDto
{
    public function __construct(
        public string $token,
        public SessionData $data
    ) {
    }
}
