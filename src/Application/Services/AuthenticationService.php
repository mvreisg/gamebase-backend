<?php

declare(strict_types=1);

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

            $user->setUserName($userName);
            $user->validateUserName();

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
            $payload = JWT::decode($token, new Key($secretKey, 'HS256'));
            return $payload;
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

    public function tryLogin(string $userName, string $passWord): bool
    {
        try {
            $requestUser = new User();

            $requestUser->setUserName($userName);
            $requestUser->setPassword($passWord);

            $requestUser->validateUserName();
            $requestUser->validatePassWord();

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

    public function validateToken(string $token): bool
    {
        try {
            $payload = $this->decodeToken($token);
            $userName = $payload->sub;
            return $this->cache->exists($userName);
        } catch (AuthenticationException $e) {
            return false;
        }
    }

    public function generateToken(string $userName, bool $oneWeek): string
    {
        try {
            $token = $this->encodeToken($userName, $oneWeek);

            $oneDayInSeconds = 60 * 60 * 24;
            $oneWeekInSeconds = $oneDayInSeconds * 7;

            $this->cache->set($userName, $token);

            if ($oneWeek) {
                $this->cache->expire($userName, $oneWeekInSeconds);
            } else {
                $this->cache->expire($userName, $oneDayInSeconds);
            }

            return $token;
        } catch (EntityInvalidValueException $e) {
            throw $e;
        }
    }

    public function checkIfTokenExists(string $userName): string|null
    {
        return $this->cache->get($userName);
    }

    public function tryLogoff(string $token): bool
    {
        try {
            $payload = $this->decodeToken($token);
            $userName = $payload->sub;
            return $this->cache->delete($userName);
        } catch (AuthenticationException $e) {
            throw $e;
        }
    }
}
