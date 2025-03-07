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

    public function checkIfHasSession(string $userName): bool
    {
        try {
            $user = new User();
            $user->validateUserName($userName);

            $token = $this->cache->get($userName);
            if ($token === null) {
                return false;
            }

            if ($token === '') {
                return false;
            }

            return true;
        } catch (EntityInvalidValueException $e) {
            throw $e;
        }
    }

    public function generateToken(string $userName, bool $oneWeek): string
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

    public function decodeToken(string $token): string
    {
        try {
            if ($token === '') {
                throw new AuthenticationException('O token está vazio!');
            }
            $secretKey = $_SERVER['JWT_SECRET'];
            $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));

            return $decoded->sub;
        } catch (AuthenticationException $e) {
            throw $e;
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

    public function validateToken(string $token): bool
    {
        try {
            $secretKey = $_SERVER['JWT_SECRET'];
            $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));

            $userName = $decoded->sub;
            $cached = $this->cache->get($userName);

            if ($cached === null) {
                throw new AuthenticationException('O token é nulo!');
            }

            if ($cached === '') {
                throw new AuthenticationException('O token está vazio!');
            }

            return true;
        } catch (AuthenticationException $e) {
            throw $e;
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

    public function setSessionToken(string $userName, string $token): void
    {
        try {
            $user = new User();
            $user->validateUserName($userName);

            if ($token === '') {
                throw new AuthenticationException('O token está vazio!');
            }
            $this->cache->set($userName, $token);
        } catch (
            AuthenticationException |
            EntityInvalidValueException $e
        ) {
            throw $e;
        }
    }

    public function getSessionToken(string $userName): string
    {
        try {
            $user = new User();
            $user->validateUserName($userName);

            $cached = $this->cache->get($userName);
            if ($cached === null) {
                throw new AuthenticationException('O token é nulo!');
            }

            if ($cached === '') {
                throw new AuthenticationException('O token é vazio!');
            }

            return $cached;
        } catch (
            AuthenticationException |
            EntityInvalidValueException $e
        ) {
            throw $e;
        }
    }

    public function logoff(string $userName): bool
    {
        try {
            $user = new User();
            $user->validateUserName($userName);

            $value = $this->cache->get($userName);
            if ($value === null) {
                return false;
            }

            if ($value === '') {
                return false;
            }

            $this->cache->set($userName, null);

            return true;
        } catch (EntityInvalidValueException $e) {
            throw $e;
        }
    }
}
