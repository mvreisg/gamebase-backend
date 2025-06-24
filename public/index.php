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
use Mvreisg\GamebaseBackend\Presentation\Routes\PermissionRoutes;
use Mvreisg\GamebaseBackend\Presentation\Routes\SectorRoutes;
use Mvreisg\GamebaseBackend\Presentation\Routes\UserPermissionRoutes;
use Mvreisg\GamebaseBackend\Presentation\Routes\UserRoutes;

include_once dirname(__DIR__) . '/vendor/autoload.php';

try {
    Dotenv\Dotenv::createImmutable(dirname(__DIR__))->load();

    $app = new HttpRouter();

    $gameRoutes = new GameRoutes();
    $gameGenreRoutes = new GameGenreRoutes();
    $gamePlatformRoutes = new GamePlatformRoutes();
    $genreRoutes = new GenreRoutes();
    $platformRoutes = new PlatformRoutes();
    $userRoutes = new UserRoutes();
    $userPermissionRoutes = new UserPermissionRoutes();
    $permissionRoutes = new PermissionRoutes();
    $sectorRoutes = new SectorRoutes();
    $authenticationRoutes = new AuthenticationRoutes();

    $gameRoutes->register($app);
    $gameGenreRoutes->register($app);
    $gamePlatformRoutes->register($app);
    $genreRoutes->register($app);
    $platformRoutes->register($app);
    $userRoutes->register($app);
    $userPermissionRoutes->register($app);
    $permissionRoutes->register($app);
    $sectorRoutes->register($app);
    $authenticationRoutes->register($app);

    $app->run();
} catch (InvalidFileException | InvalidEncodingException | Throwable $e) {
    header(HttpRouter::$STATUS_CODES[500]);    
    print('Ocorreu um erro. Contate o suporte. Código do erro: ' . $e->getCode());
}
