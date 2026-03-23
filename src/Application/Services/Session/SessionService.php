<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services\Session;

use Mvreisg\GamebaseBackend\Application\Services\Session\Login\SessionLoginInfo;
use Mvreisg\GamebaseBackend\Application\Services\Session\Login\SessionLoginResult;
use Mvreisg\GamebaseBackend\Application\Services\Session\Login\SessionLoginStates;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\State\Encoded\EncodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Decoder\AuthenticationTokenDecoder;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Encoder\AuthenticationTokenEncoder;
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

    public function tryLogin(SessionLoginInfo $info): SessionLoginResult
    {
        try {
            $fetchedUser = $this->userRepository->findByUsername(
                Username::make($info->getUsernameValue())
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

            $exists = $this->tokenCache->exists(
                Username::make($fetchedUser->getUsernameValue())
            );

            if ($exists) {
                $token = $this->tokenCache->get(
                    Username::make($fetchedUser->getUsernameValue())
                );
                $result = $this->authenticationTokenDecoder->decode($token);
                $userSectorPermissions = $this->userSectorPermissionRepository->findAllByUserId(
                    $result->getUserId()
                );
                $sessionData = new SessionData(
                    $result->getUserId(),
                    $result->getUsername(),
                    $userSectorPermissions
                );
                $duration = $result->getExpiresAt()->diff($result->getIssuedAt());
                $newToken = $this->authenticationTokenEncoder->encode(
                    $sessionData,
                    $duration
                );
                $this->tokenCache->set(
                    $result->getUsername(),
                    $newToken
                );
                $this->tokenCache->expire(
                    $result->getUsername(),
                    $duration
                );
                return new SessionLoginResult(
                    SessionLoginStates::Existing,
                    $newToken,
                    $sessionData
                );
            }

            $userSectorPermissions = $this->userSectorPermissionRepository->findAllByUserId(
                Id::make($fetchedUser->getIdValue())
            );

            $sessionData = new SessionData(
                Id::make($fetchedUser->getIdValue()),
                Username::make($fetchedUser->getUsernameValue()),
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
                $sessionData,
                $interval
            );

            $this->tokenCache->set(
                Username::make($fetchedUser->getUsernameValue()),
                $token
            );

            $this->tokenCache->expire(
                Username::make($fetchedUser->getUsernameValue()),
                $interval
            );

            return new SessionLoginResult(
                SessionLoginStates::New,
                $token,
                $sessionData
            );
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function tryLogoff(EncodedAuthenticationToken $token): void
    {
        try {
            $result = $this->authenticationTokenDecoder->decode($token);

            $this->tokenCache->delete(
                $result->getUsername()
            );
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
