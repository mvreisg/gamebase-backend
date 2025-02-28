<?php

namespace Mvreisg\GamebaseBackend\Domain\Cache;

use Predis\Response\Status;

interface UserCacheInterface
{
    public function set(string $userName, mixed $token): Status|null;

    public function get(string $userName): string|null;
}
