<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Authorization\Types\Permission;

use Mvreisg\GamebaseBackend\Domain\Entities\PermissionValue;

enum PermissionTypes: string
{
    case Create = "create";
    case Update = "update";
    case Activate = "activate";
    case List = "list";
    case Delete = "delete";

    public static function getValue(PermissionTypes $permissionType): PermissionValue
    {
        return PermissionValue::make($permissionType->value);
    }
}
