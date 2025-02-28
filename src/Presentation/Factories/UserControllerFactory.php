<?php

namespace Mvreisg\GamebaseBackend\Presentation\Factories;

use Mvreisg\GamebaseBackend\Application\Services\UserService;
use Mvreisg\GamebaseBackend\Infrastructure\Database\MariaDBConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Encryption\SodiumEncryption;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDBUserRepository;
use Mvreisg\GamebaseBackend\Presentation\Controllers\UserController;

class UserControllerFactory
{
    public static function get(): UserController
    {
        $repository = new MariaDBUserRepository(MariaDBConnection::get());
        $encrypter = new SodiumEncryption();
        $service = new UserService($repository, $encrypter);
        $controller = new UserController($service);
        return $controller;
    }
}
