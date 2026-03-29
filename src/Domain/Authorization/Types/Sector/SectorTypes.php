<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Authorization\Types\Sector;

use Mvreisg\GamebaseBackend\Domain\Entities\SectorValue;

enum SectorTypes: string
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

    public static function getValue(SectorTypes $sectorType): SectorValue
    {
        return SectorValue::make($sectorType->value);
    }
}
