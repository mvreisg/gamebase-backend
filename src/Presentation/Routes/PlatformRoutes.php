<?php

namespace Mvreisg\GamebaseBackend\Presentation\Routes;

use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRequest;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpResponse;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRouter;
use Mvreisg\GamebaseBackend\Presentation\Factories\PlatformControllerFactory;

class PlatformRoutes
{
    public function register(HttpRouter $app)
    {
        $controller = PlatformControllerFactory::get();

        $app->add('POST', '/platform', function (HttpRequest $request, HttpResponse $response) use ($controller) {
            $controller->insert($request, $response);
        });

        $app->add('GET', '/platform', function (HttpRequest $request, HttpResponse $response) use ($controller) {
            $controller->findAll($request, $response);
        });

        $app->add('GET', '/platform/:platformId', function (
            HttpRequest $request,
            HttpResponse $response
        ) use ($controller) {
            $controller->findById($request, $response);
        });

        $app->add('PATCH', '/platform/:platformId', function (
            HttpRequest $request,
            HttpResponse $response
        ) use ($controller) {
            $controller->setIsActive($request, $response);
        });

        $app->add('PUT', '/platform/:platformId', function (
            HttpRequest $request,
            HttpResponse $response
        ) use ($controller) {
            $controller->update($request, $response);
        });
    }
}
