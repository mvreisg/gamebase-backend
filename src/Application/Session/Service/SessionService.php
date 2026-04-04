<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Session\Service;

use Mvreisg\GamebaseBackend\Application\Authentication\Data\AuthenticationData;
use Mvreisg\GamebaseBackend\Application\Authentication\Services\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Authentication\Token\Cache\AuthenticationTokenCacheInterface;
use Mvreisg\GamebaseBackend\Application\Session\Data\SessionData;
use Mvreisg\GamebaseBackend\Application\Session\Exception\InvalidCredentialsException;
use Mvreisg\GamebaseBackend\Application\Session\Exception\UnexistantUserException;
use Mvreisg\GamebaseBackend\Application\Session\Login\Parameters\SessionLoginParameters;
use Mvreisg\GamebaseBackend\Application\Session\Login\Return\SessionLoginReturn;
use Mvreisg\GamebaseBackend\Domain\Encryption\Interface\EncryptionInterface;
use Mvreisg\GamebaseBackend\Domain\User\Repository\UserRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\UserSectorPermission\Entity\Collection\UserSectorPermissionCollection;
use Mvreisg\GamebaseBackend\Domain\UserSectorPermission\Repository\UserSectorPermissionRepositoryInterface;

class SessionService
{
    private UserRepositoryInterface $userRepository;
    private UserSectorPermissionRepositoryInterface $userSectorPermissionRepository;
    private EncryptionInterface $encrypter;
    private AuthenticationService $authenticationService;
    private AuthenticationTokenCacheInterface $authenticationTokenCache;

    public function __construct(
        UserRepositoryInterface $userRepository,
        UserSectorPermissionRepositoryInterface $userSectorPermissionRepository,
        EncryptionInterface $encrypter,
        AuthenticationService $authenticationService,
        AuthenticationTokenCacheInterface $authenticationTokenCache,
    ) {
        $this->userRepository = $userRepository;
        $this->userSectorPermissionRepository = $userSectorPermissionRepository;
        $this->encrypter = $encrypter;
        $this->authenticationService = $authenticationService;
        $this->authenticationTokenCache = $authenticationTokenCache;
    }

    public function login(SessionLoginParameters $parameters): SessionLoginReturn
    {
        try {
            $username = $parameters->getUsername();

            $fetchedUser = $this->userRepository->findByUsername(
                $username
            );

            if ($fetchedUser === null) {
                throw new UnexistantUserException(
                    $username
                );
            }

            $id = $fetchedUser->getId();

            $userSectorPermissions = $this->userSectorPermissionRepository->findAllByUserId(
                $id
            );

            if ($userSectorPermissions === null) {
                $userSectorPermissions = new UserSectorPermissionCollection();
            }

            $fetchedAndEncodedPassword = $fetchedUser->getPassword()->getValue();
            $decodedPassword = $this->encrypter->decrypt($fetchedAndEncodedPassword);

            $doTheTwoPasswordsMatchesEqually = strcmp(
                $parameters->getPassword()->getValue(),
                $decodedPassword
            ) === 0;

            if ($doTheTwoPasswordsMatchesEqually === false) {
                throw new InvalidCredentialsException();
            }

            $exists = $this->authenticationTokenCache->exists(
                $username->getValue()
            );

            $token = null;
            if ($exists) {
                $token = $this->authenticationTokenCache->get(
                    $username->getValue()
                );
            } else {
                $interval = null;
                $oneWeekLogin = $parameters->getOneWeekLogin();
                if ($oneWeekLogin === true) {
                    $interval = new \DateInterval("P7D");
                } else {
                    $interval = new \DateInterval("P1D");
                }

                $token = $this->authenticationService->encode(
                    new AuthenticationData(
                        $id,
                        $username
                    ),
                    $interval
                );

                $this->authenticationTokenCache->set(
                    $username->getValue(),
                    $token
                );

                $this->authenticationTokenCache->expire(
                    $username->getValue(),
                    $interval
                );
            }

            $sessionData = new SessionData(
                $id,
                $username,
                $userSectorPermissions
            );

            return new SessionLoginReturn(
                $token,
                $sessionData
            );
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function logoff(string $token): bool
    {
        try {
            $decodedToken = $this->authenticationService->validate($token);

            $wasDeleted = $this->authenticationTokenCache->delete(
                $decodedToken->getAuthenticationData()->getUsername()->getValue()
            );

            return $wasDeleted;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function retrieveData(string $token): SessionData
    {
        try {
            $decodedToken = $this->authenticationService->validate($token);

            $id = $decodedToken->getAuthenticationData()->getUserId();
            $username = $decodedToken->getAuthenticationData()->getUsername();

            $userSectorPermissions = $this->userSectorPermissionRepository->findAllByUserId(
                $id
            );

            $sessionData = new SessionData(
                $id,
                $username,
                $userSectorPermissions
            );

            return $sessionData;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
