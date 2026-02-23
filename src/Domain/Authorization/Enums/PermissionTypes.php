<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Authorization\Enums;

enum PermissionTypes: string
{
    case Create = "create";
    case Update = "update";
    case Activate = "activate";
    case List = "list";
    case Delete = "delete";
}
