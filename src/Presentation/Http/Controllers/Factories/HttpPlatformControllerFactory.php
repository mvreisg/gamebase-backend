<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controllers\Factories;

use Mvreisg\GamebaseBackend\Application\Services\Platform\PlatformService;
use Mvreisg\GamebaseBackend\Infrastructure\Connections\Pdo\PdoRepositoryConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Encryption\EncryptionAdapter;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBPlatformRepository;
use Mvreisg\GamebaseBackend\Presentation\Http\Controllers\HttpPlatformController;
use Mvreisg\GamebaseBackend\Presentation\Http\Services\Factories\Authentication\HttpAuthenticationServiceFactory;
use Mvreisg\GamebaseBackend\Presentation\Http\Services\Factories\Authorization\HttpAuthorizationServiceFactory;

class HttpPlatformControllerFactory
{
    public static function make(): HttpPlatformController
    {
        try {
            $repositoryConnection = PdoRepositoryConnection::make();

            $platformRepository = new MariaDBPlatformRepository(
                $repositoryConnection
            );

            $platformService = new PlatformService(
                $platformRepository
            );

            $encrypter = EncryptionAdapter::make();

            $authenticationService = HttpAuthenticationServiceFactory::make(
                $repositoryConnection,
                $encrypter
            );

            $authorizationService = HttpAuthorizationServiceFactory::make();

            $controller = new HttpPlatformController(
                $platformService,
                $authenticationService,
                $authorizationService
            );

            return $controller;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
