<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Factories\Router;

use Mvreisg\GamebaseBackend\Presentation\Http\Router\HttpRouter;

class HttpRouterFactory
{
    public static function make()
    {
        return new HttpRouter();
    }
}
