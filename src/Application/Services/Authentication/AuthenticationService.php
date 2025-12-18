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
use Mvreisg\GamebaseBackend\Domain\Authentication\DTOs\AuthenticationPayloadValueDTO;
use Mvreisg\GamebaseBackend\Domain\Authentication\Exceptions\AuthenticationException;
use Mvreisg\GamebaseBackend\Domain\Authentication\Interfaces\AuthenticationClockInterface;
use Mvreisg\GamebaseBackend\Domain\Authentication\ValueObjects\AuthenticationPayloadValueValueObject;
use Mvreisg\GamebaseBackend\Domain\Cache\CacheInterface;
use Mvreisg\GamebaseBackend\Domain\Cache\Exceptions\CacheException;
use Mvreisg\GamebaseBackend\Domain\Encryption\Exceptions\EncryptionException;
use Mvreisg\GamebaseBackend\Domain\Entities\Permission\Permission;
use Mvreisg\GamebaseBackend\Domain\Entities\Sector\Sector;
use Mvreisg\GamebaseBackend\Domain\Entities\User\Exceptions\UserInvalidPasswordException;
use Mvreisg\GamebaseBackend\Domain\Entities\User\Exceptions\UserInvalidUsernameException;
use Mvreisg\GamebaseBackend\Domain\Entities\User\User;
use Mvreisg\GamebaseBackend\Domain\Repositories\Exceptions\RepositoryUnexistantRegisterException;
use Mvreisg\GamebaseBackend\Domain\Repositories\PermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\SectorPermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\SectorRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\UserPermissionRepositoryInterface;

class AuthenticationService
{
    private UserRepositoryInterface $userRepository;
    private PermissionRepositoryInterface $permissionRepository;
    private SectorRepositoryInterface $sectorRepository;
    private UserPermissionRepositoryInterface $userPermissionRepository;
    private SectorPermissionRepositoryInterface $sectorPermissionRepository;
    private EncryptionInterface $encrypter;
    private CacheInterface $userCache;
    private AuthenticationInterface $authenticator;
    private AuthenticationClockInterface $authenticationClock;

    public function __construct(
        UserRepositoryInterface $userRepository,
        PermissionRepositoryInterface $permissionRepository,
        SectorRepositoryInterface $sectorRepository,
        UserPermissionRepositoryInterface $userPermissionRepository,
        SectorPermissionRepositoryInterface $sectorPermissionRepository,
        EncryptionInterface $encrypter,
        CacheInterface $userCache,
        AuthenticationInterface $authenticator,
        AuthenticationClockInterface $authenticationClock
    ) {
        $this->userRepository = $userRepository;
        $this->permissionRepository = $permissionRepository;
        $this->sectorRepository = $sectorRepository;
        $this->userPermissionRepository = $userPermissionRepository;
        $this->sectorPermissionRepository = $sectorPermissionRepository;
        $this->encrypter = $encrypter;
        $this->userCache = $userCache;
        $this->authenticator = $authenticator;
        $this->authenticationClock = $authenticationClock;
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
                $this->authenticator->decode(
                    $token,
                    $this->authenticationClock
                );
                return new AuthenticationLoginResultValueObject(
                    AuthenticationLoginExistanceStatesEnum::Existing,
                    $token
                );
            }

            $authenticationTime = $oneWeek ? AuthenticationTimesEnum::OneWeek : AuthenticationTimesEnum::OneDay;

            $userId = $fetchedUser->getId();

            $permissions = [];
            $userPermissions = $this->userPermissionRepository->findAllByUserId($userId);
            $allSectorPermissions = [];
            foreach ($userPermissions as $userPermission) {
                $permissions[] = $this->permissionRepository->findById($userPermission->getPermissionId());
                $allSectorPermissions[] = $this->sectorPermissionRepository->findAllByPermissionId(
                    $userPermission->getPermissionId()
                );
            }

            $sectors = [];
            foreach ($allSectorPermissions as $sectorPermissions) {
                foreach ($sectorPermissions as $sectorPermission) {
                    $sectors[] = $this->sectorRepository->findById($sectorPermission->getSectorId());
                }
            }

            $dto = new AuthenticationPayloadValueDTO(
                $fetchedUser->getUsername(),
                array_map(fn (Permission $p) => $p->getId(), $permissions),
                array_map(fn (Sector $s) => $s->getId(), $sectors)
            );

            $token = $this->authenticator->encode(
                $dto,
                $authenticationTime,
                $this->authenticationClock
            );

            $this->userCache->set($requestUsername, $token);
            $this->userCache->expire($requestUsername, $authenticationTime);

            return new AuthenticationLoginResultValueObject(
                AuthenticationLoginExistanceStatesEnum::New,
                $token
            );
        } catch (
            AuthenticationException |
            UserInvalidUsernameException |
            UserInvalidPasswordException |
            EncryptionException |
            CacheException
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
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function validateLogin(string $token): void
    {
        try {
            $decoded = $this->authenticator->decode(
                $token,
                $this->authenticationClock
            );

            $cachedToken = $this->userCache->get($decoded->getDto()->username);

            $existantDecoded = $this->authenticator->decode(
                $cachedToken,
                $this->authenticationClock
            );

            $isValid =
                $existantDecoded->getEmittedAt()->getTimestamp() ===
                $decoded->getEmittedAt()->getTimestamp();

            if ($isValid === false) {
                throw new AuthenticationServiceUnauthorizedException(
                    "Authentication service error: The provided token is not valid."
                );
            }

            $hasExpired =
                $this->authenticationClock->now()->getTimestamp() >=
                $decoded->getExpiresAt()->getTimestamp();

            if ($hasExpired) {
                throw new AuthenticationServiceUnauthorizedException(
                    "Authentication service error: The token expired."
                );
            }
        } catch (AuthenticationServiceUnauthorizedException $e) {
            throw $e;
        } catch (
            AuthenticationException |
            CacheException
            $e
        ) {
            throw new AuthenticationServiceUnauthorizedException(
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
            $decoded = $this->authenticator->decode(
                $token,
                $this->authenticationClock
            );

            $this->userCache->delete(
                $decoded->getDto()->username
            );
        } catch (AuthenticationServiceUnauthorizedException $e) {
            throw $e;
        } catch (
            AuthenticationException |
            CacheException
            $e
        ) {
            throw new AuthenticationServiceUnauthorizedException(
                "Authentication service error: {$e->getMessage()}",
                $e
            );
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
