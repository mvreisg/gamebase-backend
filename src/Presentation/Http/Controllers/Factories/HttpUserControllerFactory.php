<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controllers\Factories;

use Mvreisg\GamebaseBackend\Application\Services\User\UserService;
use Mvreisg\GamebaseBackend\Infrastructure\Encryption\Defuse\DefuseEncryption;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Connections\MariaDBRepositoryConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBUserRepository;
use Mvreisg\GamebaseBackend\Presentation\Http\Controllers\HttpUserController;
use Mvreisg\GamebaseBackend\Presentation\Http\Services\Factories\Authentication\HttpAuthenticationServiceFactory;

class HttpUserControllerFactory
{
    public static function make(): HttpUserController
    {
        try {
            $repositoryConnection = MariaDBRepositoryConnection::get();

            $userRepository = new MariaDBUserRepository(
                $repositoryConnection
            );

            $encrypter = new DefuseEncryption();

            $authenticationService = HttpAuthenticationServiceFactory::make();

            $userService = new UserService(
                $userRepository,
                $encrypter
            );

            $controller = new HttpUserController(
                $userService,
                $authenticationService
            );

            return $controller;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
