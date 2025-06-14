<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Routes;

use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRequest;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpResponse;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRouter;
use Mvreisg\GamebaseBackend\Presentation\Factories\UserControllerFactory;

class UserRoutes
{
    public function register(HttpRouter $app)
    {
        $controller = UserControllerFactory::get();

        $app->add('POST', '/user', function (HttpRequest $request, HttpResponse $response) use ($controller) {
            $controller->insert($request, $response);
        });

        $app->add('PUT', '/user/:id', function (HttpRequest $request, HttpResponse $response) use ($controller) {
            $controller->update($request, $response);
        });

        $app->add('PATCH', '/user/:id', function (HttpRequest $request, HttpResponse $response) use ($controller) {
            $controller->setIsActive($request, $response);
        });

        $app->add('GET', '/user/find/all', function (HttpRequest $request, HttpResponse $response) use ($controller) {
            $controller->findAll($request, $response);
        });

        $app->add('GET', '/user/find/id/:id', function (
            HttpRequest $request,
            HttpResponse $response
        ) use ($controller) {
            $controller->findById($request, $response);
        });

        $app->add('GET', '/user/find/username/:username', function (
            HttpRequest $request,
            HttpResponse $response
        ) use ($controller) {
            $controller->findByUserName($request, $response);
        });
    }
}
