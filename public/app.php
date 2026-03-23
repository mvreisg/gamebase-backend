<?php

use DI\ContainerBuilder;
use Mvreisg\GamebaseBackend\Presentation\Http\Controllers\HttpAuthenticationController;
use Mvreisg\GamebaseBackend\Presentation\Http\Controllers\HttpGameController;
use Mvreisg\GamebaseBackend\Presentation\Http\Controllers\HttpSessionController;
use Mvreisg\GamebaseBackend\Presentation\Http\Controllers\HttpUserController;
use Mvreisg\GamebaseBackend\Presentation\Http\Handlers\Exceptions\HttpMethodNotAllowedExceptionHandler;
use Mvreisg\GamebaseBackend\Presentation\Http\Handlers\Exceptions\HttpNotFoundExceptionHandler;
use Mvreisg\GamebaseBackend\Presentation\Http\Middlewares\Authentication\Token\HttpAuthenticationTokenRetrieverMiddleware;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;

try {
    require_once dirname(__DIR__) . "/constants.php";
    require_once PROJECT_ROOT . "/bootstrap.php";

    $builder = new ContainerBuilder();
    $builder->addDefinitions(PROJECT_ROOT . "/configurations/php_di/definitions.php");
    $container = $builder->build();

    AppFactory::setContainer($container);
    $app = AppFactory::create();

    $app->addBodyParsingMiddleware();

    $errorMiddleware = $app->addErrorMiddleware(true, true, true);
    $errorMiddleware->setErrorHandler(
        HttpNotFoundException::class,
        HttpNotFoundExceptionHandler::class
    );
    $errorMiddleware->setErrorHandler(
        HttpMethodNotAllowedException::class,
        HttpMethodNotAllowedExceptionHandler::class
    );

    $app->group("/session", function (RouteCollectorProxy $sessionGroup) {
        $sessionGroup->post("/login", [HttpSessionController::class, "login"]);
        $sessionGroup->delete("/logoff", [HttpSessionController::class, "logoff"])->add(HttpAuthenticationTokenRetrieverMiddleware::class);
    });

    $app->group("/authentication", function (RouteCollectorProxy $authenticationGroup) {
        $authenticationGroup->get("/validate", [HttpAuthenticationController::class, "validate"]);
    })->add(HttpAuthenticationTokenRetrieverMiddleware::class);

    $app->group("/game", function (RouteCollectorProxy $gameGroup) {
        $gameGroup->post("", [HttpGameController::class, "insert"]);
        $gameGroup->put("/{id:[0-9]+}", [HttpGameController::class, "update"]);
        $gameGroup->patch("/{id:[0-9]+}", [HttpGameController::class, "setIsActive"]);
        $gameGroup->get("/{id:[0-9]+}", [HttpGameController::class, "findById"]);
        $gameGroup->get("", [HttpGameController::class, "findAll"]);
    })->add(HttpAuthenticationTokenRetrieverMiddleware::class);

    $app->group("/user", function (RouteCollectorProxy $userGroup) {
        $userGroup->post("", [HttpUserController::class, "insert"]);
        $userGroup->put("/{id:[0-9]+}", [HttpUserController::class, "update"]);
        $userGroup->patch("/{id:[0-9]+}", [HttpUserController::class, "setIsActive"]);
        $userGroup->get("/{id:[0-9]+}", [HttpUserController::class, "findById"]);
        $userGroup->get("/{username:[a-zA-Z0-9]+}", [HttpUserController::class, "findByUsername"]);
        $userGroup->get("", [HttpUserController::class, "findAll"]);
    })->add(HttpAuthenticationTokenRetrieverMiddleware::class);

    $app->run();
} catch (\Throwable $e) {
    print_r($e);
}
