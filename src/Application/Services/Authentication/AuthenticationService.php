<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services\Authentication;

use Mvreisg\GamebaseBackend\Application\Services\Authentication\Exceptions\AuthenticationServiceInvalidCredentialsException;
use Mvreisg\GamebaseBackend\Application\Services\Authentication\Exceptions\AuthenticationServiceInvalidTokenException;
use Mvreisg\GamebaseBackend\Application\Services\Authentication\Login\AuthenticationLoginInfo;
use Mvreisg\GamebaseBackend\Application\Services\Authentication\Login\AuthenticationLoginResult;
use Mvreisg\GamebaseBackend\Application\Services\Authentication\Login\AuthenticationLoginStates;
use Mvreisg\GamebaseBackend\Application\Services\Authentication\Validation\AuthenticationValidationResult;
use Mvreisg\GamebaseBackend\Domain\Authentication\Data\AuthenticationData;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\State\Encoded\EncodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Decoder\AuthenticationTokenDecoder;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Encoder\AuthenticationTokenEncoder;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Validator\Encoded\EncodedAuthenticationTokenValidator;
use Mvreisg\GamebaseBackend\Domain\Cache\Token\Interface\TokenCacheInterface;
use Mvreisg\GamebaseBackend\Domain\Data\Id;
use Mvreisg\GamebaseBackend\Domain\Data\Username;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\UserSectorPermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\UserRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Encryption\EncryptionAdapter;

class AuthenticationService
{
    private UserRepositoryInterface $userRepository;
    private TokenCacheInterface $tokenCache;
    private EncryptionAdapter $encrypter;
    private AuthenticationTokenEncoder $authenticationTokenEncoder;
    private AuthenticationTokenDecoder $authenticationTokenDecoder;
    private UserSectorPermissionRepositoryInterface $userSectorPermissionRepository;
    private EncodedAuthenticationTokenValidator $encodedAuthenticationTokenValidator;

    public function __construct(
        UserRepositoryInterface $userRepository,
        TokenCacheInterface $tokenCache,
        EncryptionAdapter $encrypter,
        AuthenticationTokenEncoder $authenticationTokenEncoder,
        AuthenticationTokenDecoder $authenticationTokenDecoder,
        UserSectorPermissionRepositoryInterface $userSectorPermissionRepository,
        EncodedAuthenticationTokenValidator $encodedAuthenticationTokenValidator
    ) {
        $this->userRepository = $userRepository;
        $this->tokenCache = $tokenCache;
        $this->encrypter = $encrypter;
        $this->authenticationTokenEncoder = $authenticationTokenEncoder;
        $this->authenticationTokenDecoder = $authenticationTokenDecoder;
        $this->userSectorPermissionRepository = $userSectorPermissionRepository;
        $this->encodedAuthenticationTokenValidator = $encodedAuthenticationTokenValidator;
    }

    public function tryLogin(AuthenticationLoginInfo $info): AuthenticationLoginResult
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
                throw new AuthenticationServiceInvalidCredentialsException();
            }

            $exists = $this->tokenCache->exists(
                Username::make($fetchedUser->getUsernameValue())
            );

            if ($exists) {
                $token = $this->tokenCache->get(
                    Username::make($fetchedUser->getUsernameValue())
                );
                $result = $this->validateToken($token);
                return new AuthenticationLoginResult(
                    AuthenticationLoginStates::Existing,
                    $token,
                    new AuthenticationData(
                        $result->getUserId(),
                        $result->getUsername(),
                        $result->getUserSectorPermissionCollection()
                    )
                );
            }

            $userSectorPermissions = $this->userSectorPermissionRepository->findAllByUserId(
                Id::make($fetchedUser->getIdValue())
            );

            $authenticationData = new AuthenticationData(
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
                $authenticationData,
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

            return new AuthenticationLoginResult(
                AuthenticationLoginStates::New,
                $token,
                $authenticationData
            );
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function tryLogoff(EncodedAuthenticationToken $token): void
    {
        try {
            $result = $this->validateToken($token);

            $this->tokenCache->delete(
                $result->getUsername()
            );
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function validateToken(EncodedAuthenticationToken $token): AuthenticationValidationResult
    {
        try {
            $decodedToken = $this->authenticationTokenDecoder->decode($token);

            $this->userRepository->checkIfExists(
                $decodedToken->getUserId()
            );

            $cachedToken = $this->tokenCache->get(
                $decodedToken->getUsername()
            );

            $isTheTokenTheSame = strcmp(
                $token->getToken(),
                $cachedToken->getToken()
            ) === 0;

            if ($isTheTokenTheSame === false) {
                throw new AuthenticationServiceInvalidTokenException();
            }

            $this->encodedAuthenticationTokenValidator->validate($token);

            $authenticationData = new AuthenticationData(
                $decodedToken->getUserId(),
                $decodedToken->getUsername(),
                $decodedToken->getUserSectorPermissionCollection()
            );

            return new AuthenticationValidationResult(
                $authenticationData,
                $token
            );
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
