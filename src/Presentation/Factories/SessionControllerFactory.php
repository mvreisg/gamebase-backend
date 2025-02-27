<?php

namespace Mvreisg\GamebaseBackend\Presentation\Factories;

use Mvreisg\GamebaseBackend\Application\Services\SessionService;
use Mvreisg\GamebaseBackend\Application\Services\UserService;
use Mvreisg\GamebaseBackend\Infrastructure\Database\MariaDBConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Encryption\SodiumEncrypter;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDBUserRepository;
use Mvreisg\GamebaseBackend\Presentation\Controllers\SessionController;

class SessionControllerFactory
{
    public static function get(): SessionController
    {
        $repository = new MariaDBUserRepository(MariaDBConnection::get());
        $encrypter = new SodiumEncrypter();
        $userService = new UserService($repository, $encrypter);
        $sessionService = new SessionService();
        $controller = new SessionController($sessionService, $userService);
        return $controller;
    }
}
