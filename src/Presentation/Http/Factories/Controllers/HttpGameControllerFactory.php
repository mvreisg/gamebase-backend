<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Factories\Controllers;

use Mvreisg\GamebaseBackend\Application\Services\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\GameService;
use Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Jwt\JwtTokenAuthentication;
use Mvreisg\GamebaseBackend\Infrastructure\Cache\Connections\RedisConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Cache\Redis\RedisUserCache;
use Mvreisg\GamebaseBackend\Infrastructure\Encryption\Defuse\DefuseEncryption;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Connections\MariaDBConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBGameEntityRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBUserEntityRepository;
use Mvreisg\GamebaseBackend\Presentation\Http\Controllers\HttpGameController;

class HttpGameControllerFactory
{
    public static function make(): HttpGameController
    {
        $repositoryConnection = MariaDBConnection::get();
        $gameEntityRepository = new MariaDBGameEntityRepository(
            $repositoryConnection
        );

        $userEntityRepository = new MariaDBUserEntityRepository(
            $repositoryConnection
        );

        $encrypter = new DefuseEncryption();

        $cacheConnection = RedisConnection::get();

        $userCache = new RedisUserCache(
            $cacheConnection
        );

        $authenticator = new JwtTokenAuthentication();

        $gameService = new GameService(
            $gameEntityRepository
        );

        $authService = new AuthenticationService(
            $userEntityRepository,
            $encrypter,
            $userCache,
            $authenticator
        );

        $controller = new HttpGameController(
            $gameService,
            $authService
        );

        return $controller;
    }
}
