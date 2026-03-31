<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Authorization\Permission;

enum PermissionType: string
{
    case Create = "create";
    case Update = "update";
    case Activate = "activate";
    case List = "list";
    case Delete = "delete";
}
