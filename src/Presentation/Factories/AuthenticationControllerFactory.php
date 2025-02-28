<?php

namespace Mvreisg\GamebaseBackend\Presentation\Factories;

use Mvreisg\GamebaseBackend\Application\Services\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\UserService;
use Mvreisg\GamebaseBackend\Infrastructure\Database\MariaDBConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Encryption\SodiumEncryption;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDBUserRepository;
use Mvreisg\GamebaseBackend\Presentation\Controllers\AuthenticationController;

class AuthenticationControllerFactory
{
    public static function get(): AuthenticationController
    {
        $repository = new MariaDBUserRepository(MariaDBConnection::get());
        $encrypter = new SodiumEncryption();
        $authService = new AuthenticationService($repository, $encrypter);
        $userService = new UserService($repository, $encrypter);
        $controller = new AuthenticationController($authService, $userService);
        return $controller;
    }
}
