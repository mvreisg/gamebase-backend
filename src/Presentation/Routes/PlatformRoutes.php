<?php

namespace Mvreisg\GamebaseBackend\Presentation\Routes;

use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRequest;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpResponse;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRouter;
use Mvreisg\GamebaseBackend\Presentation\Factories\PlatformControllerFactory;

/**
 * Platform routes class.
 */
class PlatformRoutes
{
    /**
     * Registers the routes relatives to this entity in the router.
     * @param HttpRouter $app The HTTP router
     * @return void
     */
    public function register(HttpRouter $app)
    {
        $controller = PlatformControllerFactory::get();

        $app->add('POST', '/platform', function (HttpRequest $request, HttpResponse $response) use ($controller) {
            $controller->insert($request, $response);
        });

        $app->add('GET', '/platform', function (HttpRequest $request, HttpResponse $response) use ($controller) {
            $controller->findAll($request, $response);
        });

        $app->add('GET', '/platform/:platformId', function (HttpRequest $request, HttpResponse $response) use ($controller) {
            $controller->findById($request, $response);
        });

        $app->add('PUT', '/platform/:platformId', function (HttpRequest $request, HttpResponse $response) use ($controller) {
            $controller->update($request, $response);
        });
    }
}
