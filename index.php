<?php

namespace Mvreisg\GamebaseBackend;

use Dotenv;
use Dotenv\Exception\InvalidFileException;
use Dotenv\Exception\InvalidEncodingException;
use Mvreisg\GamebaseBackend\Presentation\Routes\GameRoutes;
use Mvreisg\GamebaseBackend\Presentation\Routes\GenreRoutes;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRouter;
use Mvreisg\GamebaseBackend\Presentation\Routes\PlatformRoutes;
use Mvreisg\GamebaseBackend\Presentation\Routes\GameGenreRoutes;
use Mvreisg\GamebaseBackend\Presentation\Routes\GamePlatformRoutes;
use Throwable;
use Mvreisg\GamebaseBackend\Presentation\Routes\AuthenticationRoutes;
use Mvreisg\GamebaseBackend\Presentation\Routes\UserRoutes;

// Includes the class autoloader.
include_once __DIR__ . '/vendor/autoload.php';

try {
    // Loads the .env file.
    Dotenv\Dotenv::createImmutable(__DIR__)->load();

    $app = new HttpRouter();

    $gameRoutes = new GameRoutes();
    $gameGenreRoutes = new GameGenreRoutes();
    $gamePlatformRoutes = new GamePlatformRoutes();
    $genreRoutes = new GenreRoutes();
    $platformRoutes = new PlatformRoutes();
    $userRoutes = new UserRoutes();
    $authenticationRoutes = new AuthenticationRoutes();

    $gameRoutes->register($app);
    $gameGenreRoutes->register($app);
    $gamePlatformRoutes->register($app);
    $genreRoutes->register($app);
    $platformRoutes->register($app);
    $userRoutes->register($app);
    $authenticationRoutes->register($app);

    $app->run();
} catch (InvalidFileException | InvalidEncodingException | Throwable $e) {
    header(HttpRouter::STATUS_CODES[500]);
    print_r('Ocorreu um erro. Contate o suporte.');
}
