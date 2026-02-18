<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Enums;

enum HttpRequestBodyPartTypes
{
    case String;
    case Int;
    case Float;
    case Bool;
}
