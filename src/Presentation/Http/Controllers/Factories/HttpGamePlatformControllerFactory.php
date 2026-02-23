<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controllers\Factories;

use Mvreisg\GamebaseBackend\Application\Services\GamePlatform\GamePlatformService;
use Mvreisg\GamebaseBackend\Infrastructure\Connections\Pdo\PdoRepositoryConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Encryption\EncryptionAdapter;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBGamePlatformRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBGameRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBPlatformRepository;
use Mvreisg\GamebaseBackend\Presentation\Http\Controllers\HttpGamePlatformController;
use Mvreisg\GamebaseBackend\Presentation\Http\Services\Factories\Authentication\HttpAuthenticationServiceFactory;
use Mvreisg\GamebaseBackend\Presentation\Http\Services\Factories\Authorization\HttpAuthorizationServiceFactory;

class HttpGamePlatformControllerFactory
{
    public static function make(): HttpGamePlatformController
    {
        try {
            $repositoryConnection = PdoRepositoryConnection::make();

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

            $encrypter = EncryptionAdapter::make();

            $authenticationService = HttpAuthenticationServiceFactory::make(
                $repositoryConnection,
                $encrypter
            );

            $authorizationService = HttpAuthorizationServiceFactory::make();

            $controller = new HttpGamePlatformController(
                $gamePlatformService,
                $authenticationService,
                $authorizationService
            );

            return $controller;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
