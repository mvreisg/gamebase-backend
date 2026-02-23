<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controllers\Factories;

use Mvreisg\GamebaseBackend\Application\Services\User\UserService;
use Mvreisg\GamebaseBackend\Infrastructure\Connections\Pdo\PdoRepositoryConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Encryption\EncryptionAdapter;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBUserRepository;
use Mvreisg\GamebaseBackend\Presentation\Http\Controllers\HttpUserController;
use Mvreisg\GamebaseBackend\Presentation\Http\Services\Factories\Authentication\HttpAuthenticationServiceFactory;
use Mvreisg\GamebaseBackend\Presentation\Http\Services\Factories\Authorization\HttpAuthorizationServiceFactory;

class HttpUserControllerFactory
{
    public static function make(): HttpUserController
    {
        try {
            $repositoryConnection = PdoRepositoryConnection::make();

            $userRepository = new MariaDBUserRepository(
                $repositoryConnection
            );

            $encrypter = EncryptionAdapter::make();

            $authenticationService = HttpAuthenticationServiceFactory::make(
                $repositoryConnection,
                $encrypter
            );

            $userService = new UserService(
                $userRepository,
                $encrypter
            );

            $authorizationService = HttpAuthorizationServiceFactory::make();

            $controller = new HttpUserController(
                $userService,
                $authenticationService,
                $authorizationService
            );

            return $controller;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
