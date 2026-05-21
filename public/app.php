<?php

use Mvreisg\GamebaseBackend\Application\Authentication\Exception\InvalidTokenException;
use Mvreisg\GamebaseBackend\Application\Authentication\Exception\UnexistantTokenException;
use Mvreisg\GamebaseBackend\Application\Authentication\Token\Cache\Exception\AuthenticationTokenCacheException;
use Mvreisg\GamebaseBackend\Application\Authentication\Token\Provider\Exception\AuthenticationTokenProviderException;
use Mvreisg\GamebaseBackend\Application\Session\Exception\InvalidCredentialsException;
use Mvreisg\GamebaseBackend\Application\Session\Exception\UnexistantUserException;
use Mvreisg\GamebaseBackend\Domain\Authorization\Exception\UnauthorizedException;
use Mvreisg\GamebaseBackend\Domain\Encryption\Interface\Exception\EncryptionInterfaceException;
use Mvreisg\GamebaseBackend\Domain\Game\Exception\GameNotFoundException;
use Mvreisg\GamebaseBackend\Domain\GameGenre\Exception\GameGenreNotFoundException;
use Mvreisg\GamebaseBackend\Domain\GamePlatform\Exception\GamePlatformNotFoundException;
use Mvreisg\GamebaseBackend\Domain\Genre\Exception\GenreNotFoundException;
use Mvreisg\GamebaseBackend\Domain\Permission\Exception\DuplicatedPermissionValueException;
use Mvreisg\GamebaseBackend\Domain\Permission\Exception\PermissionNotFoundException;
use Mvreisg\GamebaseBackend\Domain\Permission\ValueObject\Exception\EmptyPermissionValueValueException;
use Mvreisg\GamebaseBackend\Domain\Permission\ValueObject\Exception\InvalidPermissionValueValueException;
use Mvreisg\GamebaseBackend\Domain\Platform\Exception\PlatformNotFoundException;
use Mvreisg\GamebaseBackend\Domain\Sector\Exception\DuplicatedSectorValueException;
use Mvreisg\GamebaseBackend\Domain\Sector\Exception\NullPermissionValueException;
use Mvreisg\GamebaseBackend\Domain\Sector\Exception\NullSectorValueException;
use Mvreisg\GamebaseBackend\Domain\Sector\Exception\SectorNotFoundException;
use Mvreisg\GamebaseBackend\Domain\Sector\ValueObject\Exception\EmptySectorValueValueException;
use Mvreisg\GamebaseBackend\Domain\Sector\ValueObject\Exception\InvalidSectorValueValueException;
use Mvreisg\GamebaseBackend\Domain\Shared\Exception\DuplicatedNameException;
use Mvreisg\GamebaseBackend\Domain\Shared\Exception\NullIdException;
use Mvreisg\GamebaseBackend\Domain\Shared\Exception\NullIsActiveException;
use Mvreisg\GamebaseBackend\Domain\Shared\Exception\NullNameException;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Exception\InvalidIdValueException;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Name\Exception\EmptyNameValueException;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Name\Exception\InvalidNameValueException;
use Mvreisg\GamebaseBackend\Domain\User\Exception\DuplicatedUsernameException;
use Mvreisg\GamebaseBackend\Domain\User\Exception\NullPasswordException;
use Mvreisg\GamebaseBackend\Domain\User\Exception\NullUsernameException;
use Mvreisg\GamebaseBackend\Domain\User\Exception\UserNotFoundException;
use Mvreisg\GamebaseBackend\Domain\User\ValueObject\Password\Exception\EmptyPasswordValueException;
use Mvreisg\GamebaseBackend\Domain\User\ValueObject\Password\Exception\InvalidPasswordValueException;
use Mvreisg\GamebaseBackend\Domain\User\ValueObject\Username\Exception\EmptyUsernameValueException;
use Mvreisg\GamebaseBackend\Domain\User\ValueObject\Username\Exception\InvalidUsernameValueException;
use Mvreisg\GamebaseBackend\Domain\UserSectorPermission\Exception\InvalidUserSectorPermissionException;
use Mvreisg\GamebaseBackend\Domain\UserSectorPermission\Exception\UserSectorPermissionNotFoundException;
use Mvreisg\GamebaseBackend\Infrastructure\Serialization\Exception\SerializationException;
use Mvreisg\GamebaseBackend\Infrastructure\Time\Unit\Exception\TimeUnitException;
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
use Mvreisg\GamebaseBackend\Presentation\Http\Controller\Pages\Dashboard\Database\Pdo\HttpPdoDatabaseDashboardViewPageController;
use Mvreisg\GamebaseBackend\Presentation\Http\Controller\Pages\Dashboard\Database\Phinx\HttpPhinxDatabaseDashboardViewPageController;
use Mvreisg\GamebaseBackend\Presentation\Http\Controller\Pages\Dashboard\Encryption\Defuse\HttpDashboardDefuseEncryptionPageController;
use Mvreisg\GamebaseBackend\Presentation\Http\Controller\Pages\Dashboard\Encryption\Sodium\HttpDashboardSodiumEncryptionPageController;
use Mvreisg\GamebaseBackend\Presentation\Http\Controller\Pages\Dashboard\HttpDashboardHomeViewPageController;
use Mvreisg\GamebaseBackend\Presentation\Http\Controller\Pages\Api\Documentation\HttpApiDocumentationPageViewController;
use Mvreisg\GamebaseBackend\Presentation\Http\Controller\Pages\Dashboard\OpenApi\Documentation\HttpOpenApiDocumentationDashboardViewPageController;
use Mvreisg\GamebaseBackend\Presentation\Http\Controller\Pages\HttpLoginPageViewController;
use Mvreisg\GamebaseBackend\Presentation\Http\Handler\Exception\HttpUnauthorizedExceptionHandler;
use Mvreisg\GamebaseBackend\Presentation\Http\Handler\Exception\HttpBadRequestExceptionHandler;
use Mvreisg\GamebaseBackend\Presentation\Http\Handler\Exception\HttpForbiddenExceptionHandler;
use Mvreisg\GamebaseBackend\Presentation\Http\Handler\Exception\HttpInternalServerErrorExceptionHandler;
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
    $errorMiddleware
        ->setErrorHandler(
            HttpNotFoundException::class,
            HttpNotFoundExceptionHandler::class
        )
        ->setErrorHandler(
            HttpMethodNotAllowedException::class,
            HttpMethodNotAllowedExceptionHandler::class
        )
        ->setErrorHandler(
            AuthenticationTokenCacheException::class,
            HttpInternalServerErrorExceptionHandler::class
        )
        ->setErrorHandler(
            AuthenticationTokenProviderException::class,
            HttpInternalServerErrorExceptionHandler::class
        )
        ->setErrorHandler(
            InvalidTokenException::class,
            HttpForbiddenExceptionHandler::class
        )
        ->setErrorHandler(
            UnexistantTokenException::class,
            HttpNotFoundExceptionHandler::class
        )
        ->setErrorHandler(
            InvalidCredentialsException::class,
            HttpUnauthorizedExceptionHandler::class
        )
        ->setErrorHandler(
            UnexistantUserException::class,
            HttpNotFoundExceptionHandler::class
        )
        ->setErrorHandler(
            UnauthorizedException::class,
            HttpUnauthorizedExceptionHandler::class
        )
        ->setErrorHandler(
            EncryptionInterfaceException::class,
            HttpInternalServerErrorExceptionHandler::class
        )
        ->setErrorHandler(
            GameNotFoundException::class,
            HttpNotFoundExceptionHandler::class
        )
        ->setErrorHandler(
            GameGenreNotFoundException::class,
            HttpNotFoundExceptionHandler::class
        )
        ->setErrorHandler(
            GamePlatformNotFoundException::class,
            HttpNotFoundExceptionHandler::class
        )
        ->setErrorHandler(
            GenreNotFoundException::class,
            HttpNotFoundExceptionHandler::class
        )
        ->setErrorHandler(
            EmptyPermissionValueValueException::class,
            HttpBadRequestExceptionHandler::class
        )
        ->setErrorHandler(
            InvalidPermissionValueValueException::class,
            HttpBadRequestExceptionHandler::class
        )
        ->setErrorHandler(
            DuplicatedPermissionValueException::class,
            HttpBadRequestExceptionHandler::class
        )
        ->setErrorHandler(
            NullPermissionValueException::class,
            HttpBadRequestExceptionHandler::class
        )
        ->setErrorHandler(
            PermissionNotFoundException::class,
            HttpNotFoundExceptionHandler::class
        )
        ->setErrorHandler(
            PlatformNotFoundException::class,
            HttpNotFoundExceptionHandler::class
        )
        ->setErrorHandler(
            EmptySectorValueValueException::class,
            HttpBadRequestExceptionHandler::class
        )
        ->setErrorHandler(
            InvalidSectorValueValueException::class,
            HttpBadRequestExceptionHandler::class
        )
        ->setErrorHandler(
            DuplicatedSectorValueException::class,
            HttpBadRequestExceptionHandler::class
        )
        ->setErrorHandler(
            NullSectorValueException::class,
            HttpBadRequestExceptionHandler::class
        )
        ->setErrorHandler(
            SectorNotFoundException::class,
            HttpNotFoundExceptionHandler::class
        )
        ->setErrorHandler(
            EmptyNameValueException::class,
            HttpBadRequestExceptionHandler::class
        )
        ->setErrorHandler(
            InvalidIdValueException::class,
            HttpBadRequestExceptionHandler::class
        )
        ->setErrorHandler(
            InvalidNameValueException::class,
            HttpBadRequestExceptionHandler::class
        )
        ->setErrorHandler(
            DuplicatedNameException::class,
            HttpBadRequestExceptionHandler::class
        )
        ->setErrorHandler(
            NullIdException::class,
            HttpBadRequestExceptionHandler::class
        )
        ->setErrorHandler(
            NullIsActiveException::class,
            HttpBadRequestExceptionHandler::class
        )
        ->setErrorHandler(
            NullNameException::class,
            HttpBadRequestExceptionHandler::class
        )
        ->setErrorHandler(
            EmptyPasswordValueException::class,
            HttpBadRequestExceptionHandler::class
        )
        ->setErrorHandler(
            EmptyUsernameValueException::class,
            HttpBadRequestExceptionHandler::class
        )
        ->setErrorHandler(
            InvalidPasswordValueException::class,
            HttpBadRequestExceptionHandler::class
        )
        ->setErrorHandler(
            InvalidUsernameValueException::class,
            HttpBadRequestExceptionHandler::class
        )
        ->setErrorHandler(
            DuplicatedUsernameException::class,
            HttpBadRequestExceptionHandler::class
        )
        ->setErrorHandler(
            NullPasswordException::class,
            HttpBadRequestExceptionHandler::class
        )
        ->setErrorHandler(
            NullUsernameException::class,
            HttpBadRequestExceptionHandler::class
        )
        ->setErrorHandler(
            UserNotFoundException::class,
            HttpNotFoundExceptionHandler::class
        )
        ->setErrorHandler(
            InvalidUserSectorPermissionException::class,
            HttpBadRequestExceptionHandler::class
        )
        ->setErrorHandler(
            UserSectorPermissionNotFoundException::class,
            HttpNotFoundExceptionHandler::class
        )
        ->setErrorHandler(
            SerializationException::class,
            HttpInternalServerErrorExceptionHandler::class
        )
        ->setErrorHandler(
            TimeUnitException::class,
            HttpInternalServerErrorExceptionHandler::class
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

    $app->group("/pages", function (RouteCollectorProxy $pagesGroup) {
        $pagesGroup->group("/api", function (RouteCollectorProxy $apiGroup) {
            $apiGroup->get("/documentation", HttpApiDocumentationPageViewController::class);
        });
        $pagesGroup->get("/login", HttpLoginPageViewController::class);
        $pagesGroup->group("/dashboard", function (RouteCollectorProxy $dashboardGroup) {
            $dashboardGroup->get("/home", HttpDashboardHomeViewPageController::class);
            $dashboardGroup->group("/open_api", function (RouteCollectorProxy $openApiGroup) {
                $openApiGroup->get("/documentation", HttpOpenApiDocumentationDashboardViewPageController::class);
            });
            $dashboardGroup->group("/database", function (RouteCollectorProxy $databaseGroup) {
                $databaseGroup->group("/pdo", function (RouteCollectorProxy $pdoGroup) {
                    $pdoGroup->get("/view", HttpPdoDatabaseDashboardViewPageController::class);
                });
                $databaseGroup->group("/phinx", function (RouteCollectorProxy $phinxGroup) {
                    $phinxGroup->get("/view", HttpPhinxDatabaseDashboardViewPageController::class);
                });
            });
            $dashboardGroup->group("/encryption", function (RouteCollectorProxy $encryptionGroup) {
                $encryptionGroup->group("/defuse", function (RouteCollectorProxy $defuseGroup) {
                    $defuseGroup->get("/view", HttpDashboardDefuseEncryptionPageController::class);
                });
                $encryptionGroup->group("/sodium", function (RouteCollectorProxy $sodiumGroup) {
                    $sodiumGroup->get("/view", HttpDashboardSodiumEncryptionPageController::class);
                });
            });
        });
    });

    $app->run();
} catch (\Throwable $e) {
    print_r($e);
}
