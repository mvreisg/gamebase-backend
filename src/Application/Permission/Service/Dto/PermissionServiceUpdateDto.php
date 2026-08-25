<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Permission\Service\Dto;

use Mvreisg\GamebaseBackend\Domain\Permission\ValueObject\PermissionValue\PermissionValue;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Name\Name;

final readonly class PermissionServiceUpdateDto
{
    public function __construct(
        public Id $id,
        public Name $name,
        public PermissionValue $value,
        public bool $isActive
    ) {
    }
}
