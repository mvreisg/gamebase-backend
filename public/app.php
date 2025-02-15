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
use Exception;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRequest;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpResponse;

// Includes the class autoloader.
include_once dirname(__DIR__) . '/vendor/autoload.php';

try {
    // Loads the .env file.
    Dotenv\Dotenv::createImmutable(dirname(__DIR__))->load();

    $app = new HttpRouter();

    $gameRoutes = new GameRoutes();
    $gameGenreRoutes = new GameGenreRoutes();
    $gamePlatformRoutes = new GamePlatformRoutes();
    $genreRoutes = new GenreRoutes();
    $platformRoutes = new PlatformRoutes();

    $gameRoutes->register($app);
    $gameGenreRoutes->register($app);
    $gamePlatformRoutes->register($app);
    $genreRoutes->register($app);
    $platformRoutes->register($app);

    $app->add(
        HttpRouter::WILDCARD_METHOD,
        '/',
        fn (HttpRequest $req, HttpResponse $res) => $res->status(HttpRouter::STATUS_CODES[200])->appendString('Servidor funcionando!')->send()
    );

    $app->run();
} catch (InvalidFileException | InvalidEncodingException | Exception | Throwable $e) {
    print('Ocorreu um erro. Contate o suporte.');
    var_dump($e);
    header(HttpRouter::STATUS_CODES[500]);
}
