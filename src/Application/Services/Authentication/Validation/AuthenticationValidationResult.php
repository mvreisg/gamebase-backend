<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services\Authentication\Validation;

use Mvreisg\GamebaseBackend\Domain\Authentication\Data\AuthenticationData;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\State\Encoded\EncodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Data\Id;
use Mvreisg\GamebaseBackend\Domain\Data\PermissionCollection;
use Mvreisg\GamebaseBackend\Domain\Data\SectorCollection;
use Mvreisg\GamebaseBackend\Domain\Data\SectorPermissionCollection;
use Mvreisg\GamebaseBackend\Domain\Data\Username;

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

    public function getPermissionCollection(): PermissionCollection
    {
        return $this->data->getPermissionCollection();
    }

    public function getSectorCollection(): SectorCollection
    {
        return $this->data->getSectorCollection();
    }

    public function getSectorPermissionCollection(): SectorPermissionCollection
    {
        return $this->data->getSectorPermissionCollection();
    }

    public function toArray(): array
    {
        return $this->data->toArray();
    }

    public function getToken(): EncodedAuthenticationToken
    {
        return $this->token;
    }
}
