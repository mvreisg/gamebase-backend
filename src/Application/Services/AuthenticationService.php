<?php

namespace Mvreisg\GamebaseBackend\Application\Services;

use Mvreisg\GamebaseBackend\Domain\Encryption\EncryptionInterface;
use Mvreisg\GamebaseBackend\Domain\Entities\User;
use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;
use Mvreisg\GamebaseBackend\Domain\Repositories\UserRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseFetchFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementExecutionFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\EncryptionErrorException;
use PDOException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use DateTimeImmutable;
use DomainException;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use InvalidArgumentException;
use Mvreisg\GamebaseBackend\Application\Exceptions\SessionException;
use Mvreisg\GamebaseBackend\Domain\Cache\UserCacheInterface;
use Throwable;
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
            EncryptionErrorException |
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
        $token = $this->cache->get($userName);
        if ($token === '') {
            return false;
        }
        return true;
    }

    public function generateToken(string $userName): string
    {
        try {
            $secretKey = $_SERVER['JWT_SECRET'];
            $issuedAt = new DateTimeImmutable();
            $expireAt = $issuedAt->modify('+60 seconds')->getTimestamp();

            $sub = $this->encrypter->encrypt($userName);

            $payload = [
                'iat' => $issuedAt->getTimestamp(),
                'exp' => $expireAt,
                'sub' => $sub
            ];

            $cached = $this->cache->get($userName);
            if ($cached !== '') {
                return $cached;
            }

            $token = JWT::encode($payload, $secretKey, 'HS256');

            $this->cache->set($userName, $token);

            return $token;
        } catch (Throwable $e) {
            throw $e;
        }
    }

    public function validateToken(string $token): bool
    {
        try {
            $secretKey = $_SERVER['JWT_SECRET'];
            $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));

            $sub = $decoded->sub;
            $userName = $this->encrypter->decrypt($sub);
            $cached = $this->cache->get($userName);
            $isValid = $cached !== '';

            if ($isValid === false) {
                $this->cache->set($userName, null);
            }

            return $isValid;
        } catch (InvalidArgumentException $e) {
            throw new SessionException('Objeto key inválido!', 0, $e);
        } catch (DomainException $e) {
            throw new SessionException('JWT malformado!', 0, $e);
        } catch (UnexpectedValueException $e) {
            throw new SessionException('JWT inválido!', 0, $e);
        } catch (SignatureInvalidException $e) {
            throw new SessionException('Falha na verificação de assinatura do JWT', 0, $e);
        } catch (BeforeValidException  $e) {
            throw new SessionException('JWT está tentando ser usado antes de ser elegível!', 0, $e);
        } catch (ExpiredException $e) {
            throw new SessionException('JWT expirado!', 0, $e);
        }
    }

    public function getSessionToken(string $userName)
    {
        return $this->cache->get($userName);
    }

    public function logoff(string $userName)
    {
        $this->cache->set($userName, '');
    }
}
