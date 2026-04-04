<?php

use Mvreisg\GamebaseBackend\Presentation\Http\Controller\HttpAuthenticationController;
use Mvreisg\GamebaseBackend\Presentation\Http\Controller\HttpGameController;
use Mvreisg\GamebaseBackend\Presentation\Http\Controller\HttpGameGenreController;
use Mvreisg\GamebaseBackend\Presentation\Http\Controller\HttpGamePlatformController;
use Mvreisg\GamebaseBackend\Presentation\Http\Controller\HttpGenreController;
use Mvreisg\GamebaseBackend\Presentation\Http\Controller\HttpPermissionController;
use Mvreisg\GamebaseBackend\Presentation\Http\Controller\HttpPlatformController;
use Mvreisg\GamebaseBackend\Presentation\Http\Controller\HttpSectorController;
use Mvreisg\GamebaseBackend\Presentation\Http\Controller\HttpSessionController;
use Mvreisg\GamebaseBackend\Presentation\Http\Controller\HttpUserController;
use Mvreisg\GamebaseBackend\Presentation\Http\Controller\HttpUserSectorPermissionController;
use Mvreisg\GamebaseBackend\Presentation\Http\Handler\Exception\HttpMethodNotAllowedExceptionHandler;
use Mvreisg\GamebaseBackend\Presentation\Http\Handler\Exception\HttpNotFoundExceptionHandler;
use Mvreisg\GamebaseBackend\Presentation\Http\Middleware\Authentication\Token\HttpAuthenticationTokenRetrieverMiddleware;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;

try {
    require_once dirname(__DIR__) . "/constants.php";
    require_once PROJECT_ROOT . "/bootstrap.php";

    $container = require PROJECT_ROOT . "/configurations/php_di/src/container_bootstrap.php";

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
        $sessionGroup->get("/me", [HttpSessionController::class, "retrieveData"])->add(HttpAuthenticationTokenRetrieverMiddleware::class);
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

    $app->group("/sector", function (RouteCollectorProxy $sectorGroup) {
        $sectorGroup->post("", [HttpSectorController::class, "insert"]);
        $sectorGroup->put("/{id:[0-9]+}", [HttpSectorController::class, "update"]);
        $sectorGroup->patch("/{id:[0-9]+}", [HttpSectorController::class, "setIsActive"]);
        $sectorGroup->get("/{id:[0-9]+}", [HttpSectorController::class, "findById"]);
        $sectorGroup->get("", [HttpSectorController::class, "findAll"]);
    })->add(HttpAuthenticationTokenRetrieverMiddleware::class);

    $app->group("/permission", function (RouteCollectorProxy $permissionGroup) {
        $permissionGroup->post("", [HttpPermissionController::class, "insert"]);
        $permissionGroup->put("/{id:[0-9]+}", [HttpPermissionController::class, "update"]);
        $permissionGroup->patch("/{id:[0-9]+}", [HttpPermissionController::class, "setIsActive"]);
        $permissionGroup->get("/{id:[0-9]+}", [HttpPermissionController::class, "findById"]);
        $permissionGroup->get("", [HttpPermissionController::class, "findAll"]);
    })->add(HttpAuthenticationTokenRetrieverMiddleware::class);

    $app->group("/platform", function (RouteCollectorProxy $platformGroup) {
        $platformGroup->post("", [HttpPlatformController::class, "insert"]);
        $platformGroup->put("/{id:[0-9]+}", [HttpPlatformController::class, "update"]);
        $platformGroup->patch("/{id:[0-9]+}", [HttpPlatformController::class, "setIsActive"]);
        $platformGroup->get("/{id:[0-9]+}", [HttpPlatformController::class, "findById"]);
        $platformGroup->get("", [HttpPlatformController::class, "findAll"]);
    })->add(HttpAuthenticationTokenRetrieverMiddleware::class);

    $app->group("/genre", function (RouteCollectorProxy $genreGroup) {
        $genreGroup->post("", [HttpGenreController::class, "insert"]);
        $genreGroup->put("/{id:[0-9]+}", [HttpGenreController::class, "update"]);
        $genreGroup->patch("/{id:[0-9]+}", [HttpGenreController::class, "setIsActive"]);
        $genreGroup->get("/{id:[0-9]+}", [HttpGenreController::class, "findById"]);
        $genreGroup->get("", [HttpGenreController::class, "findAll"]);
    })->add(HttpAuthenticationTokenRetrieverMiddleware::class);

    $app->group("/game_genre", function (RouteCollectorProxy $gameGenreGroup) {
        $gameGenreGroup->post("", [HttpGameGenreController::class, "insert"]);
        $gameGenreGroup->put("/{id:[0-9]+}", [HttpGameGenreController::class, "update"]);
        $gameGenreGroup->delete("/{id:[0-9]+}", [HttpGameGenreController::class, "delete"]);
        $gameGenreGroup->get("/{id:[0-9]+}", [HttpGameGenreController::class, "findById"]);
        $gameGenreGroup->get("", [HttpGameGenreController::class, "findAll"]);
    })->add(HttpAuthenticationTokenRetrieverMiddleware::class);

    $app->group("/game_platform", function (RouteCollectorProxy $gamePlatformGroup) {
        $gamePlatformGroup->post("", [HttpGamePlatformController::class, "insert"]);
        $gamePlatformGroup->put("/{id:[0-9]+}", [HttpGamePlatformController::class, "update"]);
        $gamePlatformGroup->delete("/{id:[0-9]+}", [HttpGamePlatformController::class, "delete"]);
        $gamePlatformGroup->get("/{id:[0-9]+}", [HttpGamePlatformController::class, "findById"]);
        $gamePlatformGroup->get("", [HttpGamePlatformController::class, "findAll"]);
    })->add(HttpAuthenticationTokenRetrieverMiddleware::class);

    $app->group("/user_sector_permission", function (RouteCollectorProxy $userSectorPermissionGroup) {
        $userSectorPermissionGroup->post("", [HttpUserSectorPermissionController::class, "insert"]);
        $userSectorPermissionGroup->put("/{id:[0-9]+}", [HttpUserSectorPermissionController::class, "update"]);
        $userSectorPermissionGroup->delete("/{id:[0-9]+}", [HttpUserSectorPermissionController::class, "delete"]);
        $userSectorPermissionGroup->get("/{id:[0-9]+}", [HttpUserSectorPermissionController::class, "findById"]);
        $userSectorPermissionGroup->get("", [HttpUserSectorPermissionController::class, "findAll"]);
    })->add(HttpAuthenticationTokenRetrieverMiddleware::class);

    $app->run();
} catch (\Throwable $e) {
    print_r($e);
}
