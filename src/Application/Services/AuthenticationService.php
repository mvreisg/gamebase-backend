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
use Throwable;
use UnexpectedValueException;

class AuthenticationService
{
    private UserRepositoryInterface $repository;
    private EncryptionInterface $encrypter;

    public function __construct(UserRepositoryInterface $repository, EncryptionInterface $encrypter)
    {
        $this->repository = $repository;
        $this->encrypter = $encrypter;
    }

    public function checkIfItHasCredentials(mixed $userName, mixed $passWord): bool
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

    public function generateToken(int $userId): string
    {
        try {
            $secretKey = $_SERVER['JWT_SECRET'];
            $issuedAt = new DateTimeImmutable();
            $expireAt = $issuedAt->modify('+60 seconds')->getTimestamp();

            $payload = [
                'iat' => $issuedAt->getTimestamp(),
                'exp' => $expireAt,
                'sub' => $userId
            ];

            return JWT::encode($payload, $secretKey, 'HS256');
        } catch (Throwable $e) {
            throw $e;
        }
    }

    public function validateToken(string $token): int
    {
        try {
            $secretKey = $_SERVER['JWT_SECRET'];
            $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
            return $decoded->sub;
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
}
