<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Authorization\Sector;

use Mvreisg\GamebaseBackend\Domain\Authorization\Permission\PermissionType;

enum SectorType: string
{
    case User = "user";
    case Permission = "permission";
    case Sector = "sector";
    case Game = "game";
    case Platform = "platform";
    case Genre = "genre";
    case GameGenre = "game_genre";
    case GamePlatform = "game_platform";
    case UserSectorPermission = "user_sector_permission";

    public function allow(PermissionType $permission): bool
    {
        switch ($this) {
            case self::GameGenre:
            case self::GamePlatform:
            case self::UserSectorPermission:
                return $permission !== PermissionType::Activate;
            default:
                return $permission !== PermissionType::Delete;
        }
    }
}
