<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Cache\Token\Interface;

use Mvreisg\GamebaseBackend\Domain\Authentication\Token\State\Encoded\EncodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Entities\Username;

interface TokenCacheInterface
{
    public function set(Username $username, EncodedAuthenticationToken $token): void;

    public function get(Username $username): EncodedAuthenticationToken;

    public function expire(Username $username, \DateInterval $time): void;

    public function delete(Username $username): bool;

    public function exists(Username $username): bool;
}
