<?php

namespace Mvreisg\GamebaseBackend\Presentation\Factories;

use Mvreisg\GamebaseBackend\Application\Services\AuthenticationService;
use Mvreisg\GamebaseBackend\Infrastructure\Database\MariaDBConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Encryption\SodiumEncrypter;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDBUserRepository;
use Mvreisg\GamebaseBackend\Presentation\Controllers\AuthenticationController;

class AuthenticationControllerFactory
{
    public static function get(): AuthenticationController
    {
        $repository = new MariaDBUserRepository(MariaDBConnection::get());
        $encrypter = new SodiumEncrypter();
        $service = new AuthenticationService($repository, $encrypter);
        $controller = new AuthenticationController($service);
        return $controller;
    }
}
