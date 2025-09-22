<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services;

use Mvreisg\GamebaseBackend\Application\Exceptions\Authentication\AuthenticationException;
use Mvreisg\GamebaseBackend\Domain\Encryption\EncryptionInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\UserEntityRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Authentication\Enums\AuthenticationTimesEnum;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\TokenAuthenticationInterface;
use Mvreisg\GamebaseBackend\Domain\Cache\CacheInterface;
use Mvreisg\GamebaseBackend\Domain\Entities\UserEntity;
use stdClass;

class AuthenticationService
{
    private UserEntityRepositoryInterface $userEntityRepository;
    private EncryptionInterface $encrypter;
    private CacheInterface $userCache;
    private TokenAuthenticationInterface $authenticator;

    public function __construct(
        UserEntityRepositoryInterface $userEntityRepository,
        EncryptionInterface $encrypter,
        CacheInterface $userCache,
        TokenAuthenticationInterface $authenticator
    ) {
        $this->userEntityRepository = $userEntityRepository;
        $this->encrypter = $encrypter;
        $this->userCache = $userCache;
        $this->authenticator = $authenticator;
    }

    public function encodeToken(string $userName, bool $oneWeek): string
    {
        try {
            $userEntity = new UserEntity();

            $userEntity->setUserName($userName);
            $userEntity->validateUserName();

            $validatedUserName = $userEntity->getUserName();
            $time = $oneWeek ? AuthenticationTimesEnum::OneWeek : AuthenticationTimesEnum::OneDay;

            $token = $this->authenticator->encode($validatedUserName, $time);

            return $token;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function decodeToken(string $token): stdClass
    {
        try {
            $decodedPayload = $this->authenticator->decode($token);
            return $decodedPayload;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function tryLogin(string $userName, string $passWord): void
    {
        try {
            $requestUserEntity = new UserEntity();

            $requestUserEntity->setUserName($userName);
            $requestUserEntity->setPassword($passWord);

            $requestUserEntity->validateUserName();
            $requestUserEntity->validatePassWord();

            $requestUserEntityName = $requestUserEntity->getUserName();
            $requestPassWord = $requestUserEntity->getPassWord();

            $fetchedUserEntity = $this->userEntityRepository->findByUserName($requestUserEntityName);
            if ($fetchedUserEntity === null) {
                throw new AuthenticationException(
                    'Invalid credentials!',
                );
            }

            $fetchedAndEncodedPassWord = $fetchedUserEntity->getPassWord();
            $decodedPassword = $this->encrypter->decrypt($fetchedAndEncodedPassWord);

            $doTheTwoPassWordsMatchesEqually = strcmp($requestPassWord, $decodedPassword) === 0;

            if ($doTheTwoPassWordsMatchesEqually === false) {
                throw new AuthenticationException(
                    'Invalid credentials!',
                );
            }
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function validateToken(string $token): bool
    {
        try {
            $payload = $this->decodeToken($token);
            $userName = $payload->sub;

            $userEntity = new UserEntity();
            $userEntity->setUserName($userName);
            $userEntity->validateUserName();

            $exists = $this->checkTokenExistance($userName);
            if ($exists === false) {
                throw new AuthenticationException(
                    'Unexistant token!',
                );
            }
            $newToken = $this->retrieveToken($userName);
            $newPayload = $this->decodeToken($newToken);
            $newUserName = $newPayload->sub;

            $newUserEntity = new UserEntity();
            $newUserEntity->setUserName($newUserName);
            $newUserEntity->validateUserName();

            $isSameUserName = strcmp($userName, $newUserName) === 0;
            $isSameIat = $payload->iat === $newPayload->iat;

            $isValid = $isSameUserName && $isSameIat;

            if ($isValid === false) {
                throw new AuthenticationException(
                    'Invalid token!',
                );
            }

            return $isValid;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function generateToken(string $userName, bool $oneWeek): string
    {
        try {
            $userEntity = new UserEntity();
            $userEntity->setUserName($userName);
            $userEntity->validateUserName();

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
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function deleteToken(string $userName): bool
    {
        try {
            $userEntity = new UserEntity();
            $userEntity->setUserName($userName);
            $userEntity->validateUserName();

            $exists = $this->checkTokenExistance($userName);
            if ($exists === false) {
                throw new AuthenticationException(
                    'Unexistant token!'
                );
            }

            return $this->userCache->delete($userName);
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function checkTokenExistance(string $userName): bool
    {
        try {
            $userEntity = new UserEntity();
            $userEntity->setUserName($userName);
            $userEntity->validateUserName();

            return $this->userCache->exists($userName);
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function retrieveToken(string $userName): string
    {
        try {
            $userEntity = new UserEntity();
            $userEntity->setUserName($userName);
            $userEntity->validateUserName();

            $exists = $this->checkTokenExistance($userName);
            if ($exists === false) {
                throw new AuthenticationException(
                    'Unexistant token!'
                );
            }
            return $this->userCache->get($userName);
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function tryLogoff(string $token): void
    {
        try {
            $payload = $this->decodeToken($token);
            $userName = $payload->sub;

            $userEntity = new UserEntity();
            $userEntity->setUserName($userName);
            $userEntity->validateUserName();

            $this->deleteToken($userName);
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
