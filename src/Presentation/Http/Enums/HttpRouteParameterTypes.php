<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Enums;

enum HttpRouteParameterTypes
{
    case Route;
    case Text;
    case Integer;
    case Decimal;
    case Boolean;
}
