<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Enums;

enum HttpRouteQueryTypes
{
    case String;
    case Integer;
    case Float;
    case Boolean;
}
