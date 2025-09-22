<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Factories\Entities;

use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpRoute;

class HttpRouteFactory
{
    public static function make()
    {
        return new HttpRoute();
    }
}
