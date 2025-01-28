<?php
namespace Mvreisg\GamebaseBackend\Presentation\Routers;

use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRequest;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpResponse;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpApplication;
use Mvreisg\GamebaseBackend\Presentation\Factories\GamePlatformControllerFactory;
    
class GamePlatformRouter
{
    public function register(HttpApplication $app)
    {
        $controller = GamePlatformControllerFactory::get();
    
        $app->add("POST", "/game/:gameId/platform", function (HttpRequest $request, HttpResponse $response) use ($controller) {
            $controller->insert($request, $response);
        });

        $app->add("PUT", "/game/:gameId/platform", function (HttpRequest $request, HttpResponse $response) use ($controller) {
            $controller->edit($request, $response);
        });

        $app->add("GET", "/game/:gameId/platform", function (HttpRequest $request, HttpResponse $response) use ($controller) {
            $controller->findAllPlatformsIdsByGameId($request, $response);
        });

        $app->add("DELETE", "/game/:gameId/platform", function (HttpRequest $request, HttpResponse $response) use ($controller) {
            $controller->deleteAllPlatformsByGameId($request, $response);
        });
    }
}
