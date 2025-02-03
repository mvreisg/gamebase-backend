<?php

namespace Mvreisg\GamebaseBackend;

use Dotenv;
use Dotenv\Exception\InvalidFileException;
use Dotenv\Exception\InvalidEncodingException;
use Mvreisg\GamebaseBackend\Presentation\Routers\GameRouter;
use Mvreisg\GamebaseBackend\Presentation\Routers\GenreRouter;
use Mvreisg\GamebaseBackend\Presentation\Routers\DefaultRouter;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpApplication;
use Mvreisg\GamebaseBackend\Presentation\Routers\PlatformRouter;
use Mvreisg\GamebaseBackend\Presentation\Routers\GameGenreRouter;
use Mvreisg\GamebaseBackend\Presentation\Routers\GamePlatformRouter;
use Throwable;
use Exception;

// Includes the class autoloader.
include_once __DIR__ . '/../vendor/autoload.php';

try {
    ===
    // Loads the .env file.
    Dotenv\Dotenv::createImmutable(__DIR__ . '/../')->load();

    $app = new HttpApplication();

    $defaultRouter = new DefaultRouter();
    $gameRouter = new GameRouter();
    $gameGenreRouter = new GameGenreRouter();
    $gamePlatformRouter = new GamePlatformRouter();
    $genreRouter = new GenreRouter();
    $platformRouter = new PlatformRouter();

    $defaultRouter->register($app);
    $gameRouter->register($app);
    $gameGenreRouter->register($app);
    $gamePlatformRouter->register($app);
    $genreRouter->register($app);
    $platformRouter->register($app);

    $app->run();
} catch (InvalidFileException | InvalidEncodingException | Exception | Throwable $e) {
    print($e->getMessage());
}
