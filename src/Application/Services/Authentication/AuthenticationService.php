<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services\Authentication;

use Mvreisg\GamebaseBackend\Application\Services\Authentication\Enums\AuthenticationLoginExistanceStatesEnum;
use Mvreisg\GamebaseBackend\Application\Services\Authentication\Exceptions\AuthenticationServiceCacheException;
use Mvreisg\GamebaseBackend\Application\Services\Authentication\Exceptions\AuthenticationServiceEncryptionException;
use Mvreisg\GamebaseBackend\Application\Services\Authentication\Exceptions\AuthenticationServiceUnauthorizedException;
use Mvreisg\GamebaseBackend\Application\Services\Authentication\Exceptions\AuthenticationServiceUnexistantUserException;
use Mvreisg\GamebaseBackend\Application\Services\Authentication\ValueObjects\AuthenticationLoginResultValueObject;
use Mvreisg\GamebaseBackend\Domain\Encryption\EncryptionInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\UserRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Authentication\Enums\AuthenticationTimesEnum;
use Mvreisg\GamebaseBackend\Domain\Authentication\AuthenticationInterface;
use Mvreisg\GamebaseBackend\Domain\Authentication\Exceptions\AuthenticationException;
use Mvreisg\GamebaseBackend\Domain\Cache\CacheInterface;
use Mvreisg\GamebaseBackend\Domain\Cache\Enums\CacheInterfaceDeletionStatesEnum;
use Mvreisg\GamebaseBackend\Domain\Cache\Exceptions\CacheException;
use Mvreisg\GamebaseBackend\Domain\Encryption\Exceptions\EncryptionException;
use Mvreisg\GamebaseBackend\Domain\Entities\User\Exceptions\UserInvalidPasswordException;
use Mvreisg\GamebaseBackend\Domain\Entities\User\Exceptions\UserInvalidUsernameException;
use Mvreisg\GamebaseBackend\Domain\Entities\User\User;
use Mvreisg\GamebaseBackend\Domain\Repositories\Exceptions\RepositoryUnexistantRegisterException;

class AuthenticationService
{
    private UserRepositoryInterface $userRepository;
    private EncryptionInterface $encrypter;
    private CacheInterface $userCache;
    private AuthenticationInterface $authenticator;

    public function __construct(
        UserRepositoryInterface $userRepository,
        EncryptionInterface $encrypter,
        CacheInterface $userCache,
        AuthenticationInterface $authenticator
    ) {
        $this->userRepository = $userRepository;
        $this->encrypter = $encrypter;
        $this->userCache = $userCache;
        $this->authenticator = $authenticator;
    }

    public function tryLogin(string $username, string $password, bool $oneWeek): AuthenticationLoginResultValueObject
    {
        try {
            $requestUser = new User(
                null,
                $username,
                $password
            );

            $requestUser->validateUsername();
            $requestUser->validatePassword();

            $requestUsername = $requestUser->getUsername();
            $requestPassword = $requestUser->getPassword();

            $fetchedUser = $this->userRepository->findByUsername($requestUsername);

            $fetchedAndEncodedPassWord = $fetchedUser->getPassword();
            $decodedPassword = $this->encrypter->decrypt($fetchedAndEncodedPassWord);

            $doTheTwoPassWordsMatchesEqually = strcmp($requestPassword, $decodedPassword) === 0;

            if ($doTheTwoPassWordsMatchesEqually === false) {
                throw new AuthenticationServiceUnauthorizedException(
                    "Insert the correct password for $requestUsername",
                );
            }

            $exists = $this->userCache->exists($requestUsername);
            if ($exists) {
                $token = $this->userCache->get($requestUsername);
                return new AuthenticationLoginResultValueObject(
                    AuthenticationLoginExistanceStatesEnum::Existing,
                    $token
                );
            }

            $authenticationTime = $oneWeek ? AuthenticationTimesEnum::OneWeek : AuthenticationTimesEnum::OneDay;

            $token = $this->authenticator->encode($requestUsername, $authenticationTime);

            $this->userCache->set($requestUsername, $token);
            $this->userCache->expire($requestUsername, $authenticationTime);

            return new AuthenticationLoginResultValueObject(
                AuthenticationLoginExistanceStatesEnum::New,
                $token
            );
        } catch (
            AuthenticationException |
            UserInvalidUsernameException |
            UserInvalidPasswordException
            $e
        ) {
            throw new AuthenticationServiceUnauthorizedException(
                "Authentication service error: {$e->getMessage()}",
                $e
            );
        } catch (RepositoryUnexistantRegisterException $e) {
            throw new AuthenticationServiceUnexistantUserException(
                "Authentication service error: {$e->getMessage()}",
                $e
            );
        } catch (EncryptionException $e) {
            throw new AuthenticationServiceEncryptionException(
                "Authentication service error: {$e->getMessage()}",
                $e
            );
        } catch (CacheException $e) {
            throw new AuthenticationServiceCacheException(
                "Authentication service error: {$e->getMessage()}",
                $e
            );
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function validateLogin(string $token): void
    {
        try {
            $decoded = $this->authenticator->decode($token);

            $cachedToken = $this->userCache->get($decoded->sub);

            $existantDecoded = $this->authenticator->decode($cachedToken);

            $isValid = $existantDecoded->iat === $decoded->iat;

            if ($isValid === false) {
                throw new AuthenticationServiceUnauthorizedException(
                    "Authentication service error: The provided token is not valid."
                );
            }
        } catch (AuthenticationServiceUnauthorizedException $e) {
            throw $e;
        } catch (AuthenticationException $e) {
            throw new AuthenticationServiceUnauthorizedException(
                "Authentication service error: {$e->getMessage()}",
                $e
            );
        } catch (CacheException $e) {
            throw new AuthenticationServiceCacheException(
                "Authentication service error: {$e->getMessage()}",
                $e
            );
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function tryLogoff(string $token): void
    {
        try {
            $decoded = $this->authenticator->decode($token);

            $this->userCache->delete($decoded->sub);
        } catch (
            AuthenticationServiceUnauthorizedException |
            AuthenticationServiceCacheException
            $e
        ) {
            throw $e;
        } catch (AuthenticationException $e) {
            throw new AuthenticationServiceUnauthorizedException(
                "Authentication service error: {$e->getMessage()}",
                $e
            );
        } catch (CacheException $e) {
            throw new AuthenticationServiceCacheException(
                "Authentication service error: {$e->getMessage()}",
                $e
            );
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
