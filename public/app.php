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
use Mvreisg\GamebaseBackend\Presentation\Http\Controller\Pages\HttpLoginViewPageController;
use Mvreisg\GamebaseBackend\Presentation\Http\Handler\Exception\Application\Authentication\HttpInvalidTokenExceptionHandler;
use Mvreisg\GamebaseBackend\Presentation\Http\Handler\Exception\Application\Authentication\HttpUnexistantTokenExceptionHandler;
use Mvreisg\GamebaseBackend\Presentation\Http\Handler\Exception\Application\Authentication\Token\Cache\HttpAuthenticationTokenCacheExceptionHandler;
use Mvreisg\GamebaseBackend\Presentation\Http\Handler\Exception\Application\Authentication\Token\Provider\HttpAuthenticationTokenProviderExceptionHandler;
use Mvreisg\GamebaseBackend\Presentation\Http\Handler\Exception\Application\Session\HttpInvalidCredentialsExceptionHandler;
use Mvreisg\GamebaseBackend\Presentation\Http\Handler\Exception\Application\Session\HttpUnexistantUserExceptionHandler;
use Mvreisg\GamebaseBackend\Presentation\Http\Handler\Exception\Domain\Authorization\HttpUnauthorizedExceptionHandler;
use Mvreisg\GamebaseBackend\Presentation\Http\Handler\Exception\Domain\Encryption\HttpEncryptionInterfaceExceptionHandler;
use Mvreisg\GamebaseBackend\Presentation\Http\Handler\Exception\Domain\Game\HttpGameNotFoundExceptionHandler;
use Mvreisg\GamebaseBackend\Presentation\Http\Handler\Exception\Domain\GameGenre\HttpGameGenreNotFoundExceptionHandler;
use Mvreisg\GamebaseBackend\Presentation\Http\Handler\Exception\Domain\GamePlatform\HttpGamePlatformNotFoundExceptionHandler;
use Mvreisg\GamebaseBackend\Presentation\Http\Handler\Exception\Domain\Genre\HttpGenreNotFoundExceptionHandler;
use Mvreisg\GamebaseBackend\Presentation\Http\Handler\Exception\Domain\Permission\HttpDuplicatedPermissionValueExceptionHandler;
use Mvreisg\GamebaseBackend\Presentation\Http\Handler\Exception\Domain\Permission\HttpNullPermissionValueExceptionHandler;
use Mvreisg\GamebaseBackend\Presentation\Http\Handler\Exception\Domain\Permission\HttpPermissionNotFoundExceptionHandler;
use Mvreisg\GamebaseBackend\Presentation\Http\Handler\Exception\Domain\Permission\ValueObject\HttpEmptyPermissionValueValueExceptionHandler;
use Mvreisg\GamebaseBackend\Presentation\Http\Handler\Exception\Domain\Permission\ValueObject\HttpInvalidPermissionValueValueExceptionHandler;
use Mvreisg\GamebaseBackend\Presentation\Http\Handler\Exception\Domain\Sector\HttpDuplicatedSectorValueExceptionHandler;
use Mvreisg\GamebaseBackend\Presentation\Http\Handler\Exception\Domain\Sector\HttpNullSectorValueExceptionHandler;
use Mvreisg\GamebaseBackend\Presentation\Http\Handler\Exception\Domain\Sector\HttpSectorNotFoundExceptionHandler;
use Mvreisg\GamebaseBackend\Presentation\Http\Handler\Exception\Domain\Sector\ValueObject\HttpEmptySectorValueValueExceptionHandler;
use Mvreisg\GamebaseBackend\Presentation\Http\Handler\Exception\Domain\Sector\ValueObject\HttpInvalidSectorValueValueExceptionHandler;
use Mvreisg\GamebaseBackend\Presentation\Http\Handler\Exception\Domain\Shared\HttpDuplicatedNameExceptionHandler;
use Mvreisg\GamebaseBackend\Presentation\Http\Handler\Exception\Domain\Shared\HttpNullIdExceptionHandler;
use Mvreisg\GamebaseBackend\Presentation\Http\Handler\Exception\Domain\Shared\HttpNullIsActiveExceptionHandler;
use Mvreisg\GamebaseBackend\Presentation\Http\Handler\Exception\Domain\Shared\HttpNullNameExceptionHandler;
use Mvreisg\GamebaseBackend\Presentation\Http\Handler\Exception\Domain\Shared\ValueObject\HttpEmptyNameValueExceptionHandler;
use Mvreisg\GamebaseBackend\Presentation\Http\Handler\Exception\Domain\Shared\ValueObject\HttpInvalidIdValueExceptionHandler;
use Mvreisg\GamebaseBackend\Presentation\Http\Handler\Exception\Domain\Shared\ValueObject\HttpInvalidNameValueExceptionHandler;
use Mvreisg\GamebaseBackend\Presentation\Http\Handler\Exception\Domain\User\HttpDuplicatedUsernameExceptionHandler;
use Mvreisg\GamebaseBackend\Presentation\Http\Handler\Exception\Domain\User\HttpNullPasswordExceptionHandler;
use Mvreisg\GamebaseBackend\Presentation\Http\Handler\Exception\Domain\User\HttpNullUsernameExceptionHandler;
use Mvreisg\GamebaseBackend\Presentation\Http\Handler\Exception\Domain\User\HttpUserNotFoundExceptionHandler;
use Mvreisg\GamebaseBackend\Presentation\Http\Handler\Exception\Domain\User\ValueObject\HttpEmptyPasswordValueExceptionHandler;
use Mvreisg\GamebaseBackend\Presentation\Http\Handler\Exception\Domain\User\ValueObject\HttpEmptyUsernameValueExceptionHandler;
use Mvreisg\GamebaseBackend\Presentation\Http\Handler\Exception\Domain\User\ValueObject\HttpInvalidPasswordValueExceptionHandler;
use Mvreisg\GamebaseBackend\Presentation\Http\Handler\Exception\Domain\User\ValueObject\HttpInvalidUsernameValueExceptionHandler;
use Mvreisg\GamebaseBackend\Presentation\Http\Handler\Exception\Domain\UserSectorPermission\HttpInvalidUserSectorPermissionExceptionHandler;
use Mvreisg\GamebaseBackend\Presentation\Http\Handler\Exception\Domain\UserSectorPermission\HttpUserSectorPermissionNotFoundExceptionHandler;
use Mvreisg\GamebaseBackend\Presentation\Http\Handler\Exception\HttpMethodNotAllowedExceptionHandler;
use Mvreisg\GamebaseBackend\Presentation\Http\Handler\Exception\HttpNotFoundExceptionHandler;
use Mvreisg\GamebaseBackend\Presentation\Http\Handler\Exception\HttpPlatformNotFoundExceptionHandler;
use Mvreisg\GamebaseBackend\Presentation\Http\Middleware\Authentication\Token\HttpAuthenticationTokenRetrieverMiddleware;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

try {
    require_once dirname(__DIR__) . "/constants.php";
    require_once PROJECT_ROOT . "/bootstrap.php";

    $container = require PROJECT_ROOT . "/configurations/php_di/src/container_bootstrap.php";

    $loader = new FilesystemLoader(PROJECT_ROOT . "/src/Presentation/Http/Views");
    $twig = new Environment($loader);

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
            HttpAuthenticationTokenCacheExceptionHandler::class
        )
        ->setErrorHandler(
            AuthenticationTokenProviderException::class,
            HttpAuthenticationTokenProviderExceptionHandler::class
        )
        ->setErrorHandler(
            InvalidTokenException::class,
            HttpInvalidTokenExceptionHandler::class
        )
        ->setErrorHandler(
            UnexistantTokenException::class,
            HttpUnexistantTokenExceptionHandler::class
        )
        ->setErrorHandler(
            InvalidCredentialsException::class,
            HttpInvalidCredentialsExceptionHandler::class
        )
        ->setErrorHandler(
            UnexistantUserException::class,
            HttpUnexistantUserExceptionHandler::class
        )
        ->setErrorHandler(
            UnauthorizedException::class,
            HttpUnauthorizedExceptionHandler::class
        )
        ->setErrorHandler(
            EncryptionInterfaceException::class,
            HttpEncryptionInterfaceExceptionHandler::class
        )
        ->setErrorHandler(
            GameNotFoundException::class,
            HttpGameNotFoundExceptionHandler::class
        )
        ->setErrorHandler(
            GameGenreNotFoundException::class,
            HttpGameGenreNotFoundExceptionHandler::class
        )
        ->setErrorHandler(
            GamePlatformNotFoundException::class,
            HttpGamePlatformNotFoundExceptionHandler::class
        )
        ->setErrorHandler(
            GenreNotFoundException::class,
            HttpGenreNotFoundExceptionHandler::class
        )
        ->setErrorHandler(
            EmptyPermissionValueValueException::class,
            HttpEmptyPermissionValueValueExceptionHandler::class
        )
        ->setErrorHandler(
            InvalidPermissionValueValueException::class,
            HttpInvalidPermissionValueValueExceptionHandler::class
        )
        ->setErrorHandler(
            DuplicatedPermissionValueException::class,
            HttpDuplicatedPermissionValueExceptionHandler::class
        )
        ->setErrorHandler(
            NullPermissionValueException::class,
            HttpNullPermissionValueExceptionHandler::class
        )
        ->setErrorHandler(
            PermissionNotFoundException::class,
            HttpPermissionNotFoundExceptionHandler::class
        )
        ->setErrorHandler(
            PlatformNotFoundException::class,
            HttpPlatformNotFoundExceptionHandler::class
        )
        ->setErrorHandler(
            EmptySectorValueValueException::class,
            HttpEmptySectorValueValueExceptionHandler::class
        )
        ->setErrorHandler(
            InvalidSectorValueValueException::class,
            HttpInvalidSectorValueValueExceptionHandler::class
        )
        ->setErrorHandler(
            DuplicatedSectorValueException::class,
            HttpDuplicatedSectorValueExceptionHandler::class
        )
        ->setErrorHandler(
            NullSectorValueException::class,
            HttpNullSectorValueExceptionHandler::class
        )
        ->setErrorHandler(
            SectorNotFoundException::class,
            HttpSectorNotFoundExceptionHandler::class
        )
        ->setErrorHandler(
            SectorNotFoundException::class,
            HttpSectorNotFoundExceptionHandler::class
        )
        ->setErrorHandler(
            EmptyNameValueException::class,
            HttpEmptyNameValueExceptionHandler::class
        )
        ->setErrorHandler(
            InvalidIdValueException::class,
            HttpInvalidIdValueExceptionHandler::class
        )
        ->setErrorHandler(
            InvalidNameValueException::class,
            HttpInvalidNameValueExceptionHandler::class
        )
        ->setErrorHandler(
            DuplicatedNameException::class,
            HttpDuplicatedNameExceptionHandler::class
        )
        ->setErrorHandler(
            NullIdException::class,
            HttpNullIdExceptionHandler::class
        )
        ->setErrorHandler(
            NullIsActiveException::class,
            HttpNullIsActiveExceptionHandler::class
        )
        ->setErrorHandler(
            NullNameException::class,
            HttpNullNameExceptionHandler::class
        )
        ->setErrorHandler(
            EmptyPasswordValueException::class,
            HttpEmptyPasswordValueExceptionHandler::class
        )
        ->setErrorHandler(
            EmptyUsernameValueException::class,
            HttpEmptyUsernameValueExceptionHandler::class
        )
        ->setErrorHandler(
            InvalidPasswordValueException::class,
            HttpInvalidPasswordValueExceptionHandler::class
        )
        ->setErrorHandler(
            InvalidUsernameValueException::class,
            HttpInvalidUsernameValueExceptionHandler::class
        )
        ->setErrorHandler(
            DuplicatedUsernameException::class,
            HttpDuplicatedUsernameExceptionHandler::class
        )
        ->setErrorHandler(
            NullPasswordException::class,
            HttpNullPasswordExceptionHandler::class
        )
        ->setErrorHandler(
            NullUsernameException::class,
            HttpNullUsernameExceptionHandler::class
        )
        ->setErrorHandler(
            UserNotFoundException::class,
            HttpUserNotFoundExceptionHandler::class
        )
        ->setErrorHandler(
            InvalidUserSectorPermissionException::class,
            HttpInvalidUserSectorPermissionExceptionHandler::class
        )
        ->setErrorHandler(
            UserSectorPermissionNotFoundException::class,
            HttpUserSectorPermissionNotFoundExceptionHandler::class
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
        $pagesGroup->get("/login", HttpLoginViewPageController::class);
        $pagesGroup->group("/dashboard", function (RouteCollectorProxy $dashboardGroup) {
            $dashboardGroup->get("/home", HttpDashboardHomeViewPageController::class);
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
