<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Entities\Factories;

use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpRoutePart;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpRouteParameterTypes;

class HttpRoutePartFactory
{
    public static function make(string $name, HttpRouteParameterTypes $type): HttpRoutePart
    {
        return new HttpRoutePart($name, $type);
    }
}
