<?php

namespace Mvreisg\GamebaseBackend\Application\Services;

use Mvreisg\GamebaseBackend\Domain\Encryption\EncryptionInterface;
use Mvreisg\GamebaseBackend\Domain\Entities\User;
use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;
use Mvreisg\GamebaseBackend\Domain\Repositories\UserRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseFetchFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementExecutionFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\EncryptionException;
use PDOException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use DateTimeImmutable;
use DomainException;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use InvalidArgumentException;
use Mvreisg\GamebaseBackend\Application\Exceptions\AuthenticationException;
use Mvreisg\GamebaseBackend\Domain\Cache\UserCacheInterface;
use stdClass;
use UnexpectedValueException;

class AuthenticationService
{
    private UserRepositoryInterface $repository;
    private EncryptionInterface $encrypter;
    private UserCacheInterface $cache;

    public function __construct(
        UserRepositoryInterface $repository,
        EncryptionInterface $encrypter,
        UserCacheInterface $cache
    ) {
        $this->repository = $repository;
        $this->encrypter = $encrypter;
        $this->cache = $cache;
    }

    private function encodeToken(string $userName, bool $oneWeek)
    {
        try {
            $user = new User();
            $user->validateUserName($userName);
            $time = $oneWeek ? '+1 week' : '+1 day';
            $secretKey = $_SERVER['JWT_SECRET'];
            $issuedAt = new DateTimeImmutable();
            $expireAt = $issuedAt->modify($time)->getTimestamp();

            $payload = [
                'iat' => $issuedAt->getTimestamp(),
                'exp' => $expireAt,
                'sub' => $userName
            ];

            $token = JWT::encode($payload, $secretKey, 'HS256');

            return $token;
        } catch (EntityInvalidValueException $e) {
            throw $e;
        }
    }

    private function decodeToken(string $token): stdClass
    {
        try {
            $secretKey = $_SERVER['JWT_SECRET'];
            $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));

            return $decoded;
        } catch (InvalidArgumentException $e) {
            throw new AuthenticationException('Objeto key inválido!', 0, $e);
        } catch (DomainException $e) {
            throw new AuthenticationException('JWT malformado!', 0, $e);
        } catch (UnexpectedValueException $e) {
            throw new AuthenticationException('JWT inválido!', 0, $e);
        } catch (SignatureInvalidException $e) {
            throw new AuthenticationException('Falha na verificação de assinatura do JWT', 0, $e);
        } catch (BeforeValidException  $e) {
            throw new AuthenticationException('JWT está tentando ser usado antes de ser elegível!', 0, $e);
        } catch (ExpiredException $e) {
            throw new AuthenticationException('JWT expirado!', 0, $e);
        }
    }

    public function login(mixed $userName, mixed $passWord): bool
    {
        try {
            $requestUser = new User();
            $requestUser->validateUserName($userName);
            $requestUser->validatePassWord($passWord);
            $requestUser->setUserName($userName);
            $requestUser->setPassword($passWord);
            $requestUserName = $requestUser->getUserName();
            $requestPassWord = $requestUser->getPassWord();

            $fetchUser = $this->repository->findByUserName($requestUserName);
            if ($fetchUser === null) {
                return false;
            }

            $fetchedAndEncodedPassWord = $fetchUser->getPassWord();
            $decodedPassword = $this->encrypter->decrypt($fetchedAndEncodedPassWord);

            $doTheTwoPassWordsMatchesEqually = strcmp($requestPassWord, $decodedPassword) === 0;

            return $doTheTwoPassWordsMatchesEqually;
        } catch (
            EncryptionException |
            EntityInvalidValueException |
            DatabaseFetchFailureException |
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException $e
        ) {
            throw $e;
        }
    }

    public function generateToken(string $userName, bool $oneWeek): string
    {
        return $this->encodeToken($userName, $oneWeek);
    }

    public function validateToken(string $userName, string $token): void
    {
        try {
            $decoded = $this->decodeToken($token);
            $decodedUserName = $decoded->sub;
            $hasDecodedFailed = false;
            $hasParameterFailed = false;

            $decodedFetchUser = $this->repository->findByUserName($decodedUserName);
            if ($decodedFetchUser === null) {
                $hasDecodedFailed = true;
            }

            $parameterFetchUser = $this->repository->findByUserName($userName);
            if ($parameterFetchUser === null) {
                $hasParameterFailed = true;
            }

            if ($hasDecodedFailed || $hasParameterFailed) {
                throw new AuthenticationException('Usuário não encontrado!');
            }

            $decodedFetchedUserName = $decodedFetchUser->getUserName();
            $parameterFetchUserName = $parameterFetchUser->getUserName();

            $doTheTwoUserNamesMatchesEqually = strcmp(
                $decodedFetchedUserName,
                $parameterFetchUserName
            ) === 0;

            if ($doTheTwoUserNamesMatchesEqually === false) {
                throw new AuthenticationException('Token inválido!');
            }

            $decodedToken = $this->getSessionToken($decodedFetchedUserName);
            $parameterToken = $this->getSessionToken($parameterFetchUserName);

            $invalidToken = false;
            if ($decodedToken === null || $decodedToken === "") {
                $invalidToken = true;
            }

            if ($parameterToken === null || $parameterToken === "") {
                $invalidToken = true;
            }

            if ($invalidToken) {
                throw new AuthenticationException('Token inválido!');
            }
        } catch (
            EntityInvalidValueException |
            AuthenticationException $e
        ) {
            $this->logoff($userName, null);
            throw $e;
        }
    }

    public function setSessionToken(string $userName, string|null $token): void
    {
        try {
            $user = new User();
            $user->validateUserName($userName);

            $this->cache->set($userName, $token);
        } catch (EntityInvalidValueException $e) {
            throw $e;
        }
    }

    public function getSessionToken(string $userName): string|null
    {
        try {
            $user = new User();
            $user->validateUserName($userName);

            $cached = $this->cache->get($userName);

            return $cached;
        } catch (EntityInvalidValueException $e) {
            throw $e;
        }
    }

    public function logoff(string $userName): void
    {
        try {
            $this->setSessionToken($userName, null);
        } catch (EntityInvalidValueException $e) {
            throw $e;
        }
    }
}
