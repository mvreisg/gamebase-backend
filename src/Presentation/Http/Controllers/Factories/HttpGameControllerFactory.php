<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controllers\Factories;

use Mvreisg\GamebaseBackend\Application\Services\Game\GameService;
use Mvreisg\GamebaseBackend\Infrastructure\Connections\Pdo\PdoRepositoryConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Encryption\EncryptionAdapter;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBGameRepository;
use Mvreisg\GamebaseBackend\Presentation\Http\Controllers\HttpGameController;
use Mvreisg\GamebaseBackend\Presentation\Http\Services\Factories\Authentication\HttpAuthenticationServiceFactory;
use Mvreisg\GamebaseBackend\Presentation\Http\Services\Factories\Authorization\HttpAuthorizationServiceFactory;

class HttpGameControllerFactory
{
    public static function make(): HttpGameController
    {
        try {
            $repositoryConnection = PdoRepositoryConnection::make();

            $gameRepository = new MariaDBGameRepository(
                $repositoryConnection
            );

            $encrypter = EncryptionAdapter::make();

            $authenticationService = HttpAuthenticationServiceFactory::make();

            $gameService = new GameService(
                $gameRepository
            );

            $authorizationService = HttpAuthorizationServiceFactory::make();

            $controller = new HttpGameController(
                $gameService,
                $authenticationService,
                $authorizationService
            );

            return $controller;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
