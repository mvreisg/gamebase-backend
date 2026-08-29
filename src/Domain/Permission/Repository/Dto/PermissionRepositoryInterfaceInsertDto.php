<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Permission\Repository\Dto;

use Mvreisg\GamebaseBackend\Domain\Permission\ValueObject\PermissionValue\PermissionValue;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Name\Name;

final readonly class PermissionRepositoryInterfaceInsertDto
{
    public function __construct(
        public Name $name,
        public PermissionValue $value,
        public bool $isActive
    ) {
    }
}
