<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Entities\Factories;

use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpRoute;

class HttpRouteFactory
{
    public static function make()
    {
        return new HttpRoute();
    }
}
