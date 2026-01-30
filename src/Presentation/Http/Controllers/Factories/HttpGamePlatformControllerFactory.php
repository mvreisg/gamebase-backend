<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controllers\Factories;

use Mvreisg\GamebaseBackend\Application\Services\GamePlatform\GamePlatformService;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Connections\MariaDBRepositoryConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBGamePlatformRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBGameRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBPlatformRepository;
use Mvreisg\GamebaseBackend\Presentation\Http\Controllers\HttpGamePlatformController;
use Mvreisg\GamebaseBackend\Presentation\Http\Services\Factories\Authentication\HttpAuthenticationServiceFactory;

class HttpGamePlatformControllerFactory
{
    public static function make(): HttpGamePlatformController
    {
        try {
            $repositoryConnection = MariaDBRepositoryConnection::get();

            $gameRepository = new MariaDBGameRepository(
                $repositoryConnection
            );

            $platformRepository = new MariaDBPlatformRepository(
                $repositoryConnection
            );

            $gamePlatformRepository = new MariaDBGamePlatformRepository(
                $repositoryConnection
            );

            $gamePlatformService = new GamePlatformService(
                $gameRepository,
                $platformRepository,
                $gamePlatformRepository
            );

            $authenticationService = HttpAuthenticationServiceFactory::make();

            $controller = new HttpGamePlatformController(
                $gamePlatformService,
                $authenticationService
            );

            return $controller;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
