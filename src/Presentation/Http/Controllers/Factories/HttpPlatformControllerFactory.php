<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controllers\Factories;

use Mvreisg\GamebaseBackend\Application\Services\Platform\PlatformService;
use Mvreisg\GamebaseBackend\Infrastructure\Encryption\Defuse\DefuseEncryption;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Connections\MariaDBRepositoryConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBPlatformRepository;
use Mvreisg\GamebaseBackend\Presentation\Http\Controllers\HttpPlatformController;
use Mvreisg\GamebaseBackend\Presentation\Http\Services\Factories\Authentication\HttpAuthenticationServiceFactory;

class HttpPlatformControllerFactory
{
    public static function make(): HttpPlatformController
    {
        try {
            $repositoryConnection = MariaDBRepositoryConnection::get();

            $platformRepository = new MariaDBPlatformRepository(
                $repositoryConnection
            );

            $platformService = new PlatformService(
                $platformRepository
            );

            $encrypter = new DefuseEncryption();

            $authenticationService = HttpAuthenticationServiceFactory::make(
                $repositoryConnection,
                $encrypter
            );

            $controller = new HttpPlatformController(
                $platformService,
                $authenticationService
            );

            return $controller;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
