<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controllers\Factories;

use Mvreisg\GamebaseBackend\Application\Services\Authentication\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\Game\GameService;
use Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Jwt\Clock\JwtAuthenticationTokenClock;
use Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Jwt\Validator\Decoded\JwtDecodedAuthenticationTokenValidator;
use Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Jwt\Validator\Encoded\JwtEncodedAuthenticationTokenValidator;
use Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Jwt\Decoder\JwtAuthenticationTokenDecoder;
use Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Jwt\Encoder\JwtAuthenticationTokenEncoder;
use Mvreisg\GamebaseBackend\Infrastructure\Cache\Redis\Connection\RedisConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Cache\Redis\Token\RedisTokenCache;
use Mvreisg\GamebaseBackend\Infrastructure\Encryption\Defuse\DefuseEncryption;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Connections\MariaDBRepositoryConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBGameRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBPermissionRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBSectorPermissionRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBSectorRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBUserPermissionRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBUserRepository;
use Mvreisg\GamebaseBackend\Presentation\Http\Controllers\HttpGameController;

class HttpGameControllerFactory
{
    public static function make(): HttpGameController
    {
        try {
            $repositoryConnection = MariaDBRepositoryConnection::get();

            $gameRepository = new MariaDBGameRepository(
                $repositoryConnection
            );

            $userRepository = new MariaDBUserRepository(
                $repositoryConnection
            );

            $permissionRepository = new MariaDBPermissionRepository(
                $repositoryConnection
            );

            $sectorRepository = new MariaDBSectorRepository(
                $repositoryConnection
            );

            $userPermissionRepository = new MariaDBUserPermissionRepository(
                $repositoryConnection
            );

            $sectorPermissionRepository = new MariaDBSectorPermissionRepository(
                $repositoryConnection
            );

            $encrypter = new DefuseEncryption();

            $cacheConnection = RedisConnection::get();

            $tokenCache = new RedisTokenCache(
                $cacheConnection
            );

            $jwtAuthenticationTokenClock = new JwtAuthenticationTokenClock();

            $authenticationTokenEncoder = new JwtAuthenticationTokenEncoder(
                $jwtAuthenticationTokenClock
            );

            $authenticationTokenDecoder = new JwtAuthenticationTokenDecoder(
                $jwtAuthenticationTokenClock
            );

            $decodedAuthenticationTokenValidator = new JwtDecodedAuthenticationTokenValidator(
                $jwtAuthenticationTokenClock
            );

            $encodedAuthenticationTokenValidator = new JwtEncodedAuthenticationTokenValidator(
                $authenticationTokenDecoder,
                $decodedAuthenticationTokenValidator
            );

            $authenticationService = new AuthenticationService(
                $userRepository,
                $tokenCache,
                $encrypter,
                $authenticationTokenEncoder,
                $authenticationTokenDecoder,
                $permissionRepository,
                $sectorRepository,
                $sectorPermissionRepository,
                $userPermissionRepository,
                $encodedAuthenticationTokenValidator
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
