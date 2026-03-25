<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services\Session;

use Mvreisg\GamebaseBackend\Application\Services\Session\Login\Parameters\SessionLoginParameters;
use Mvreisg\GamebaseBackend\Application\Services\Session\Login\Return\SessionLoginReturn;
use Mvreisg\GamebaseBackend\Domain\Authentication\Data\AuthenticationData;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\State\Encoded\EncodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Decoder\AuthenticationTokenDecoder;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Encoder\AuthenticationTokenEncoder;
use Mvreisg\GamebaseBackend\Domain\Authorization\Exceptions\UnauthorizedException;
use Mvreisg\GamebaseBackend\Domain\Cache\Token\Interface\TokenCacheInterface;
use Mvreisg\GamebaseBackend\Domain\Encryption\Interface\EncryptionInterface;
use Mvreisg\GamebaseBackend\Domain\Entities\Id;
use Mvreisg\GamebaseBackend\Domain\Entities\Username;
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
    private UserSectorPermissionRepositoryInterface $userSectorPermissionRepository;

    public function __construct(
        UserRepositoryInterface $userRepository,
        TokenCacheInterface $tokenCache,
        EncryptionInterface $encrypter,
        AuthenticationTokenEncoder $authenticationTokenEncoder,
        AuthenticationTokenDecoder $authenticationTokenDecoder,
        UserSectorPermissionRepositoryInterface $userSectorPermissionRepository,
    ) {
        $this->userRepository = $userRepository;
        $this->tokenCache = $tokenCache;
        $this->encrypter = $encrypter;
        $this->authenticationTokenEncoder = $authenticationTokenEncoder;
        $this->authenticationTokenDecoder = $authenticationTokenDecoder;
        $this->userSectorPermissionRepository = $userSectorPermissionRepository;
    }

    public function login(SessionLoginParameters $info): SessionLoginReturn
    {
        try {
            $username = Username::make($info->getUsernameValue());

            $fetchedUser = $this->userRepository->findByUsername(
                $username
            );

            $fetchedAndEncodedPassword = $fetchedUser->getPasswordValue();
            $decodedPassword = $this->encrypter->decrypt($fetchedAndEncodedPassword);

            $doTheTwoPasswordsMatchesEqually = strcmp(
                $info->getPasswordValue(),
                $decodedPassword
            ) === 0;

            if ($doTheTwoPasswordsMatchesEqually === false) {
                throw new InvalidCredentialsException();
            }

            $id = Id::make($fetchedUser->getIdValue());

            $userSectorPermissions = $this->userSectorPermissionRepository->findAllByUserId(
                $id
            );

            $sessionData = new SessionData(
                $id,
                $username,
                $userSectorPermissions
            );

            $interval = new \DateInterval("P0D");
            $oneWeekLogin = $info->getOneWeekLogin();
            if ($oneWeekLogin === true) {
                $interval->d = 7;
            } else {
                $interval->d = 1;
            }

            $token = $this->authenticationTokenEncoder->encode(
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
            $result = $this->authenticationTokenDecoder->decode($token);

            $id = $result->getUserId();
            $username = $result->getUsername();

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
