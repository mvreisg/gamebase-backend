<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services\Authentication\Validation;

use Mvreisg\GamebaseBackend\Domain\Authentication\Data\AuthenticationData;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\State\Encoded\EncodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Data\Id;
use Mvreisg\GamebaseBackend\Domain\Data\Username;
use Mvreisg\GamebaseBackend\Domain\Data\UserSectorPermissionCollection;

class AuthenticationValidationResult
{
    private AuthenticationData $data;
    private EncodedAuthenticationToken $token;

    public function __construct(
        AuthenticationData $data,
        EncodedAuthenticationToken $token
    ) {
        $this->data = $data;
        $this->token = $token;
    }

    public function getUserId(): Id
    {
        return $this->data->getUserId();
    }

    public function getUsername(): Username
    {
        return $this->data->getUsername();
    }

    public function getUserSectorPermissionCollection(): UserSectorPermissionCollection
    {
        return $this->data->getUserSectorPermissionCollection();
    }

    public function toArray(): array
    {
        return $this->data->toArray();
    }

    public function toSnakeCaseArray(): array
    {
        return $this->data->toSnakeCaseArray();
    }

    public function getToken(): EncodedAuthenticationToken
    {
        return $this->token;
    }
}
