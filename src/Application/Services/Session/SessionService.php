<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services\Session;

use Mvreisg\GamebaseBackend\Application\Services\Session\Login\Parameters\SessionLoginParameters;
use Mvreisg\GamebaseBackend\Application\Services\Session\Login\Return\SessionLoginReturn;
use Mvreisg\GamebaseBackend\Domain\Authentication\Data\AuthenticationData;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Data\Encoded\EncodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Decoder\AuthenticationTokenDecoder;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Encoder\AuthenticationTokenEncoder;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Validate\AuthenticationTokenValidator;
use Mvreisg\GamebaseBackend\Domain\Authorization\Exceptions\UnauthorizedException;
use Mvreisg\GamebaseBackend\Domain\Cache\Token\Interface\TokenCacheInterface;
use Mvreisg\GamebaseBackend\Domain\Encryption\Interface\EncryptionInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\UserSectorPermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\UserRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Session\Data\SessionData;
use Mvreisg\GamebaseBackend\Domain\Session\Exceptions\InvalidCredentialsException;

class SessionService
{
    private UserRepositoryInterface $userRepository;
    private TokenCacheInterface $tokenCache;
    private EncryptionInterface $encrypter;
    private AuthenticationTokenEncoder $authenticationTokenEncoder;
    private AuthenticationTokenDecoder $authenticationTokenDecoder;
    private AuthenticationTokenValidator $authenticationTokenValidator;
    private UserSectorPermissionRepositoryInterface $userSectorPermissionRepository;

    public function __construct(
        UserRepositoryInterface $userRepository,
        TokenCacheInterface $tokenCache,
        EncryptionInterface $encrypter,
        AuthenticationTokenEncoder $authenticationTokenEncoder,
        AuthenticationTokenDecoder $authenticationTokenDecoder,
        AuthenticationTokenValidator $authenticationTokenValidator,
        UserSectorPermissionRepositoryInterface $userSectorPermissionRepository,
    ) {
        $this->userRepository = $userRepository;
        $this->tokenCache = $tokenCache;
        $this->encrypter = $encrypter;
        $this->authenticationTokenEncoder = $authenticationTokenEncoder;
        $this->authenticationTokenDecoder = $authenticationTokenDecoder;
        $this->authenticationTokenValidator = $authenticationTokenValidator;
        $this->userSectorPermissionRepository = $userSectorPermissionRepository;
    }

    public function login(SessionLoginParameters $parameters): SessionLoginReturn
    {
        try {
            $fetchedUser = $this->userRepository->findByUsername(
                $parameters->getUsername()
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

            $userSectorPermissions = $this->userSectorPermissionRepository->findAllByUserId(
                $fetchedUser->getId()
            );

            $sessionData = new SessionData(
                $fetchedUser->getId(),
                $fetchedUser->getUsername(),
                $userSectorPermissions
            );

            $interval = null;
            $oneWeekLogin = $parameters->getOneWeekLogin();
            if ($oneWeekLogin === true) {
                $interval = new \DateInterval("P7D");
            } else {
                $interval = new \DateInterval("P1D");
            }

            $token = $this->authenticationTokenEncoder->encode(
                new AuthenticationData(
                    $fetchedUser->getId(),
                    $fetchedUser->getUsername()
                ),
                $interval
            );

            $this->tokenCache->set(
                $fetchedUser->getUsername(),
                $token
            );

            $this->tokenCache->expire(
                $fetchedUser->getUsername(),
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
            $result = $this->authenticationTokenDecoder->decode($token);

            $wasDeleted = $this->tokenCache->delete(
                $result->getUsername()
            );

            return $wasDeleted;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function retrieveData(EncodedAuthenticationToken $token): SessionData
    {
        try {
            $decodedToken = $this->authenticationTokenDecoder->decode($token);

            $this->authenticationTokenValidator->validate($decodedToken);

            $id = $decodedToken->getUserId();
            $username = $decodedToken->getUsername();

            $exists = $this->tokenCache->exists(
                $username
            );

            if ($exists === false) {
                throw new UnauthorizedException();
            }

            $cachedToken = $this->tokenCache->get(
                $username
            );

            $isTokensIdenticals = strcmp(
                $token->getToken(),
                $cachedToken->getToken()
            ) === 0;

            if ($isTokensIdenticals === false) {
                throw new UnauthorizedException();
            }

            $cachedResult = $this->authenticationTokenDecoder->decode($token);

            $isIdIdenticals = $id->getValue() === $cachedResult->getUserId()->getValue();

            if ($isIdIdenticals === false) {
                throw new UnauthorizedException();
            }

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
