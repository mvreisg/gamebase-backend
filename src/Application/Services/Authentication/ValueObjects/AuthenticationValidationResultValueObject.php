<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services\Authentication\ValueObjects;

use Mvreisg\GamebaseBackend\Domain\Authentication\DTOs\AuthenticationPayloadValueDTO;

class AuthenticationValidationResultValueObject
{
    private AuthenticationPayloadValueDTO $dto;

    public function __construct(
        AuthenticationPayloadValueDTO $dto
    ) {
        $this->dto = $dto;
    }

    public function getDto(): AuthenticationPayloadValueDTO
    {
        return $this->dto;
    }
}
