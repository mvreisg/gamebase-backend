<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services\Session;

use Mvreisg\GamebaseBackend\Application\Services\Authentication\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\Session\Login\Parameters\SessionLoginParameters;
use Mvreisg\GamebaseBackend\Application\Services\Session\Login\Return\SessionLoginReturn;
use Mvreisg\GamebaseBackend\Domain\Authentication\Data\AuthenticationData;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Data\Encoded\EncodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Cache\Token\Interface\TokenCacheInterface;
use Mvreisg\GamebaseBackend\Domain\Encryption\Interface\EncryptionInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\UserSectorPermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\UserRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Session\Data\SessionData;
use Mvreisg\GamebaseBackend\Domain\Session\Exceptions\InvalidCredentialsException;

class SessionService
{
    private AuthenticationService $authenticationService;
    private UserRepositoryInterface $userRepository;
    private TokenCacheInterface $tokenCache;
    private EncryptionInterface $encrypter;
    private UserSectorPermissionRepositoryInterface $userSectorPermissionRepository;

    public function __construct(
        AuthenticationService $authenticationService,
        UserRepositoryInterface $userRepository,
        TokenCacheInterface $tokenCache,
        EncryptionInterface $encrypter,
        UserSectorPermissionRepositoryInterface $userSectorPermissionRepository,
    ) {
        $this->authenticationService = $authenticationService;
        $this->userRepository = $userRepository;
        $this->tokenCache = $tokenCache;
        $this->encrypter = $encrypter;
        $this->userSectorPermissionRepository = $userSectorPermissionRepository;
    }

    public function login(SessionLoginParameters $parameters): SessionLoginReturn
    {
        try {
            $username = $parameters->getUsername();

            $fetchedUser = $this->userRepository->findByUsername(
                $username
            );

            $id = $fetchedUser->getId();

            $userSectorPermissions = $this->userSectorPermissionRepository->findAllByUserId(
                $id
            );

            $sessionData = new SessionData(
                $id,
                $username,
                $userSectorPermissions
            );

            $fetchedAndEncodedPassword = $fetchedUser->getPassword()->getValue();
            $decodedPassword = $this->encrypter->decrypt($fetchedAndEncodedPassword);

            $doTheTwoPasswordsMatchesEqually = strcmp(
                $parameters->getPassword()->getValue(),
                $decodedPassword
            ) === 0;

            if ($doTheTwoPasswordsMatchesEqually === false) {
                throw new InvalidCredentialsException();
            }

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

            $this->tokenCache->set(
                $username,
                $token
            );

            $this->tokenCache->expire(
                $username,
                $interval
            );

            return new SessionLoginReturn(
                $token,
                $sessionData
            );
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function logoff(EncodedAuthenticationToken $token): bool
    {
        try {
            $decodedToken = $this->authenticationService->validate($token);

            $wasDeleted = $this->tokenCache->delete(
                $decodedToken->getUsername()
            );

            return $wasDeleted;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function retrieveData(EncodedAuthenticationToken $token): SessionData
    {
        try {
            $decodedToken = $this->authenticationService->validate($token);

            $id = $decodedToken->getUserId();
            $username = $decodedToken->getUsername();

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
