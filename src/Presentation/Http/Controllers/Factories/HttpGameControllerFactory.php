<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controllers\Factories;

use Mvreisg\GamebaseBackend\Application\Services\Game\GameService;
use Mvreisg\GamebaseBackend\Infrastructure\Encryption\EncryptionAdapter;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Connections\MariaDBRepositoryConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBGameRepository;
use Mvreisg\GamebaseBackend\Presentation\Http\Controllers\HttpGameController;
use Mvreisg\GamebaseBackend\Presentation\Http\Services\Factories\Authentication\HttpAuthenticationServiceFactory;

class HttpGameControllerFactory
{
    public static function make(): HttpGameController
    {
        try {
            $repositoryConnection = MariaDBRepositoryConnection::get();

            $gameRepository = new MariaDBGameRepository(
                $repositoryConnection
            );

            $encrypter = new EncryptionAdapter();

            $authenticationService = HttpAuthenticationServiceFactory::make(
                $repositoryConnection,
                $encrypter
            );

            $gameService = new GameService(
                $gameRepository
            );

            $controller = new HttpGameController(
                $gameService,
                $authenticationService
            );

            return $controller;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
