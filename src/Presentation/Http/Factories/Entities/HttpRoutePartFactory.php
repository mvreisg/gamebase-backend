<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Factories\Entities;

use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpRoutePart;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpRouteParameterTypesEnum;

class HttpRoutePartFactory
{
    public static function make(string $name, HttpRouteParameterTypesEnum $type): HttpRoutePart
    {
        return new HttpRoutePart($name, $type);
    }
}
