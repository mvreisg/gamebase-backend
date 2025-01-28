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

include_once("./../vendor/autoload.php");

try {
    Dotenv\Dotenv::createImmutable("./../")->safeLoad();
} catch (InvalidFileException $e) {
    print_r($e);
    return;
} catch (InvalidEncodingException $e) {
    print_r($e);
    return;
}

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
