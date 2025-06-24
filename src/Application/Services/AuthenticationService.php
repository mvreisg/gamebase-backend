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
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseUnexistantRegisterException;
use stdClass;
use UnexpectedValueException;

class AuthenticationService
{
    private UserRepositoryInterface $userRepository;
    private EncryptionInterface $encrypter;
    private UserCacheInterface $userCache;

    public function __construct(
        UserRepositoryInterface $userRepository,
        EncryptionInterface $encrypter,
        UserCacheInterface $userCache
    ) {
        $this->userRepository = $userRepository;
        $this->encrypter = $encrypter;
        $this->userCache = $userCache;
    }

    public function encodeToken(string $userName, bool $oneWeek): string
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

    public function decodeToken(string $token): stdClass
    {
        try {
            $secretKey = $_SERVER['JWT_SECRET'];
            $payload = JWT::decode($token, new Key($secretKey, 'HS256'));
            return $payload;
        } catch (InvalidArgumentException $e) {
            throw new AuthenticationException('Objeto key inválido!', 0x00000001, $e);
        } catch (DomainException $e) {
            throw new AuthenticationException('JWT malformado!', 0x00000002, $e);
        } catch (UnexpectedValueException $e) {
            throw new AuthenticationException('JWT inválido!', 0x00000003, $e);
        } catch (SignatureInvalidException $e) {
            throw new AuthenticationException('Falha na verificação de assinatura do JWT', 0x00000004, $e);
        } catch (BeforeValidException  $e) {
            throw new AuthenticationException('JWT está tentando ser usado antes de ser elegível!', 0x00000005, $e);
        } catch (ExpiredException $e) {
            throw new AuthenticationException('JWT expirado!', 0x00000006, $e);
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

            $fetchUser = $this->userRepository->findByUserName($requestUserName);                  
            if ($fetchUser === null) {
                throw new AuthenticationException(
                    'Usuário ou senha inválidos!'
                );
            }

            $fetchedAndEncodedPassWord = $fetchUser->getPassWord();
            $decodedPassword = $this->encrypter->decrypt($fetchedAndEncodedPassWord);            

            $doTheTwoPassWordsMatchesEqually = strcmp($requestPassWord, $decodedPassword) === 0;

            if ($doTheTwoPassWordsMatchesEqually === false){
                throw new AuthenticationException(
                    'Usuário ou senha inválidos!'
                );
            }

            return $doTheTwoPassWordsMatchesEqually;
        } catch (
            DatabaseUnexistantRegisterException $e
        ) {
            throw new AuthenticationException(
                'Usuário ou senha inválidos!'
            );
        } catch (
            AuthenticationException | 
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

            $user = new User();
            $user->setUserName($userName);
            $user->validateUserName();

            $exists = $this->checkTokenExistance($userName);            
            if ($exists === false){
                throw new AuthenticationException('Token não existe!');
            }
            $newToken = $this->retrieveToken($userName);
            $newPayload = $this->decodeToken($newToken);
            $newUserName = $newPayload->sub;

            $newUser = new User();
            $newUser->setUserName($newUserName);
            $newUser->validateUserName();

            $isValid = strcmp($userName, $newUserName) === 0;

            return $isValid;
        } catch (
            EntityInvalidValueException | 
            AuthenticationException $e
        ) {
            throw $e;
        }
    }

    public function generateToken(string $userName, bool $oneWeek): string
    {
        try {
            $user = new User();
            $user->setUserName($userName);
            $user->validateUserName();

            $token = $this->encodeToken($userName, $oneWeek);

            $oneDayInSeconds = 60 * 60 * 24;
            $oneWeekInSeconds = $oneDayInSeconds * 7;

            $this->userCache->set($userName, $token);

            if ($oneWeek) {
                $this->userCache->expire($userName, $oneWeekInSeconds);
            } else {
                $this->userCache->expire($userName, $oneDayInSeconds);
            }

            return $token;
        } catch (
            EntityInvalidValueException $e
        ) {
            throw $e;
        }
    }

    public function deleteToken(string $userName): bool
    {
        try
        {            
            $user = new User();
            $user->setUserName($userName);
            $user->validateUserName();            

            $exists = $this->checkTokenExistance($userName);
            if ($exists === false){
                throw new AuthenticationException(
                    'O token não existe!'
                );
            }

            return $this->userCache->delete($userName);
        } catch (
            AuthenticationException | 
            EntityInvalidValueException $e
        ) {
            throw $e;
        }
    }    

    public function checkTokenExistance(string $userName): bool
    {
        try{
            $user = new User();
            $user->setUserName($userName);
            $user->validateUserName();   

            return $this->userCache->exists($userName);
        } catch (
            EntityInvalidValueException $e
        ) {
            throw $e;
        }        
    }    

    public function retrieveToken(string $userName): string
    {
        try{
            $user = new User();
            $user->setUserName($userName);
            $user->validateUserName();   

            $exists = $this->checkTokenExistance($userName);
            if ($exists === false){
                throw new AuthenticationException(
                    'O token não existe!'
                );
            }
            return $this->userCache->get($userName);            
        } catch (
            AuthenticationException | 
            EntityInvalidValueException $e
        ) {
            throw $e;
        }
    }

    public function tryLogoff(string $token): bool
    {
        try {
            $payload = $this->decodeToken($token);
            $userName = $payload->sub;

            $user = new User();
            $user->setUserName($userName);
            $user->validateUserName();   

            return $this->deleteToken($userName);
        } catch (
            EntityInvalidValueException | 
            AuthenticationException $e
        ) {
            throw $e;
        }
    }
}
