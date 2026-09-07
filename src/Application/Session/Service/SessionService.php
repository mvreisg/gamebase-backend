<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Session\Service;

use Mvreisg\GamebaseBackend\Domain\Authentication\Data\AuthenticationData;
use Mvreisg\GamebaseBackend\Domain\Authentication\Service\AuthenticationService;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Cache\AuthenticationTokenCacheInterface;
use Mvreisg\GamebaseBackend\Domain\Session\Data\SessionData;
use Mvreisg\GamebaseBackend\Application\Session\Service\Dto\SessionServiceLoginInputDto;
use Mvreisg\GamebaseBackend\Application\Session\Service\Dto\SessionServiceLoginOutputDto;
use Mvreisg\GamebaseBackend\Domain\Encryption\Interface\EncryptionInterface;
use Mvreisg\GamebaseBackend\Domain\Session\Exception\InvalidCredentialsException;
use Mvreisg\GamebaseBackend\Domain\Session\Exception\UnexistantUserException;
use Mvreisg\GamebaseBackend\Domain\Shared\Interface\ClockInterface;
use Mvreisg\GamebaseBackend\Domain\User\Repository\UserRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\UserSectorPermission\Entity\Collection\UserSectorPermissionCollection;
use Mvreisg\GamebaseBackend\Domain\UserSectorPermission\Repository\UserSectorPermissionRepositoryInterface;
use Psr\Log\LoggerInterface;

class SessionService
{
    private UserRepositoryInterface $userRepository;
    private UserSectorPermissionRepositoryInterface $userSectorPermissionRepository;
    private EncryptionInterface $encrypter;
    private AuthenticationService $authenticationService;
    private AuthenticationTokenCacheInterface $authenticationTokenCache;
    private LoggerInterface $logger;
    private ClockInterface $clock;

    public function __construct(
        UserRepositoryInterface $userRepository,
        UserSectorPermissionRepositoryInterface $userSectorPermissionRepository,
        EncryptionInterface $encrypter,
        AuthenticationService $authenticationService,
        AuthenticationTokenCacheInterface $authenticationTokenCache,
        LoggerInterface $logger,
        ClockInterface $clock
    ) {
        $this->userRepository = $userRepository;
        $this->userSectorPermissionRepository = $userSectorPermissionRepository;
        $this->encrypter = $encrypter;
        $this->authenticationService = $authenticationService;
        $this->authenticationTokenCache = $authenticationTokenCache;
        $this->logger = $logger;
        $this->clock = $clock;
    }

    public function login(SessionServiceLoginInputDto $dto): SessionServiceLoginOutputDto
    {
        try {
            $username = $dto->username;

            $fetchedUser = $this->userRepository->findByUsername(
                $username
            );

            if ($fetchedUser === null) {
                throw new UnexistantUserException(
                    $username
                );
            }

            $id = $fetchedUser->getId();

            $fetchedAndEncodedPassword = $fetchedUser->getPassword()->getValue();
            $decodedPassword = $this->encrypter->decrypt($fetchedAndEncodedPassword);

            $doTheTwoPasswordsMatchesEqually = strcmp(
                $dto->password->getValue(),
                $decodedPassword
            ) === 0;

            if ($doTheTwoPasswordsMatchesEqually === false) {
                $this->logger->warning("Invalid credentials provided for login attempt", [
                    "username" => $username->getValue(),
                    "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
                ]);
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
                $oneWeekLogin = $dto->oneWeekLogin;
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

            $sessionData = $this->retrieveData(
                $token
            );

            $this->logger->info("User logged in successfully", [
                "username" => $username->getValue(),
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);

            return new SessionServiceLoginOutputDto(
                $token,
                $sessionData
            );
        } catch (\Throwable $e) {
            $this->logger->error("An error occurred during login", [
                "exception" => $e->getMessage(),
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);
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

            $this->logger->info("User logoff has been tried", [
                "username" => $decodedToken->getAuthenticationData()->getUsername()->getValue(),
                "wasDeleted" => $wasDeleted,
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);

            return $wasDeleted;
        } catch (\Throwable $e) {
            $this->logger->error("An error occurred during logoff", [
                "exception" => $e->getMessage(),
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);
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

            if ($userSectorPermissions === null) {
                $userSectorPermissions = new UserSectorPermissionCollection();
            }

            $sessionData = new SessionData(
                $id,
                $username,
                $userSectorPermissions
            );

            return $sessionData;
        } catch (\Throwable $e) {
            $this->logger->error("An error occurred during session data retrieval", [
                "exception" => $e->getMessage(),
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);
            throw $e;
        }
    }
}
