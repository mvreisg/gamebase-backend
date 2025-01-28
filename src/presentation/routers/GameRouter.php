<?php
namespace Mvreisg\GamebaseBackend\Presentation\Routers;

use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRequest;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpResponse;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpApplication;
use Mvreisg\GamebaseBackend\Presentation\Factories\GameControllerFactory;
    
class GameRouter
{
    public function register(HttpApplication $app)
    {
        $gameController = GameControllerFactory::get();
    
        $app->add("POST", "/game", function (HttpRequest $request, HttpResponse $response) use ($gameController) {
            $gameController->insert($request, $response);
        });

        $app->add("GET", "/game", function (HttpRequest $request, HttpResponse $response) use ($gameController) {
            $gameController->findAll($request, $response);
        });
            
        $app->add("GET", "/game/:gameId", function (HttpRequest $request, HttpResponse $response) use ($gameController) {
            $gameController->findById($request, $response);
        });

        $app->add("PUT", "/game/:gameId", function (HttpRequest $request, HttpResponse $response) use ($gameController) {
            $gameController->edit($request, $response);
        });
    }
}
