<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Session\Service;

use Mvreisg\GamebaseBackend\Application\Authentication\Data\AuthenticationData;
use Mvreisg\GamebaseBackend\Application\Authentication\Token\Jwt\Services\JwtTokenAuthenticationService;
use Mvreisg\GamebaseBackend\Application\Session\Data\SessionData;
use Mvreisg\GamebaseBackend\Application\Session\Exceptions\InvalidCredentialsException;
use Mvreisg\GamebaseBackend\Application\Session\Login\Parameters\SessionLoginParameters;
use Mvreisg\GamebaseBackend\Application\Session\Login\Return\SessionLoginReturn;
use Mvreisg\GamebaseBackend\Domain\Encryption\Interface\EncryptionInterface;
use Mvreisg\GamebaseBackend\Domain\User\Repository\UserRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\UserSectorPermission\Repository\UserSectorPermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Jwt\Data\Encoded\JwtEncodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Infrastructure\Cache\Predis\Token\PredisTokenCache;

class JwtSessionService
{
    private JwtTokenAuthenticationService $authenticationService;
    private UserRepositoryInterface $userRepository;
    private PredisTokenCache $tokenCache;
    private EncryptionInterface $encrypter;
    private UserSectorPermissionRepositoryInterface $userSectorPermissionRepository;

    public function __construct(
        JwtTokenAuthenticationService $authenticationService,
        UserRepositoryInterface $userRepository,
        PredisTokenCache $tokenCache,
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

    public function logoff(JwtEncodedAuthenticationToken $token): bool
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

    public function retrieveData(JwtEncodedAuthenticationToken $token): SessionData
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
