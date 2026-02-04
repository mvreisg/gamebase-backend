<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services\Authentication;

use Mvreisg\GamebaseBackend\Application\Services\Authentication\Exceptions\AuthenticationServiceInvalidCredentialsException;
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
use Mvreisg\GamebaseBackend\Domain\Data\PermissionCollection;
use Mvreisg\GamebaseBackend\Domain\Data\SectorCollection;
use Mvreisg\GamebaseBackend\Domain\Data\Username;
use Mvreisg\GamebaseBackend\Domain\Encryption\Interface\EncryptionInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\PermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\SectorPermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\SectorRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\UserPermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\UserRepositoryInterface;

class AuthenticationService
{
    private UserRepositoryInterface $userRepository;
    private TokenCacheInterface $tokenCache;
    private EncryptionInterface $encrypter;
    private AuthenticationTokenEncoder $authenticationTokenEncoder;
    private AuthenticationTokenDecoder $authenticationTokenDecoder;
    private PermissionRepositoryInterface $permissionRepository;
    private SectorRepositoryInterface $sectorRepository;
    private SectorPermissionRepositoryInterface $sectorPermissionRepository;
    private UserPermissionRepositoryInterface $userPermissionRepository;
    private EncodedAuthenticationTokenValidator $encodedAuthenticationTokenValidator;

    public function __construct(
        UserRepositoryInterface $userRepository,
        TokenCacheInterface $tokenCache,
        EncryptionInterface $encrypter,
        AuthenticationTokenEncoder $authenticationTokenEncoder,
        AuthenticationTokenDecoder $authenticationTokenDecoder,
        PermissionRepositoryInterface $permissionRepository,
        SectorRepositoryInterface $sectorRepository,
        SectorPermissionRepositoryInterface $sectorPermissionRepository,
        UserPermissionRepositoryInterface $userPermissionRepository,
        EncodedAuthenticationTokenValidator $encodedAuthenticationTokenValidator
    ) {
        $this->userRepository = $userRepository;
        $this->tokenCache = $tokenCache;
        $this->encrypter = $encrypter;
        $this->authenticationTokenEncoder = $authenticationTokenEncoder;
        $this->authenticationTokenDecoder = $authenticationTokenDecoder;
        $this->permissionRepository = $permissionRepository;
        $this->sectorRepository = $sectorRepository;
        $this->sectorPermissionRepository = $sectorPermissionRepository;
        $this->userPermissionRepository = $userPermissionRepository;
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
                        $result->getPermissionCollection(),
                        $result->getSectorCollection()
                    )
                );
            }

            $permissions = new PermissionCollection(null);
            $userPermissions = $this->userPermissionRepository->findAllByUserId(
                Id::make($fetchedUser->getIdValue())
            );
            foreach ($userPermissions->fetchAll() as $userPermission) {
                $fetchedPermission = $this->permissionRepository->findById(
                    Id::make($userPermission->getPermissionIdValue())
                );
                $permissions->add($fetchedPermission);
            }
            $sectors = new SectorCollection(null);
            foreach ($permissions->fetchAll() as $permission) {
                $sectorPermissions = $this->sectorPermissionRepository->findAllByPermissionId(
                    Id::make($permission->getIdValue())
                );
                foreach ($sectorPermissions->fetchAll() as $sectorPermission) {
                    $fetchedSector = $this->sectorRepository->findById(
                        Id::make($sectorPermission->getSectorIdValue())
                    );
                    $exists = $sectors->exists(Id::make($fetchedSector->getIdValue()));
                    if ($exists === false) {
                        $sectors->add($fetchedSector);
                    }
                }
            }

            $authenticationData = new AuthenticationData(
                Id::make($fetchedUser->getIdValue()),
                Username::make($fetchedUser->getUsernameValue()),
                $permissions,
                $sectors
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
                throw new \DomainException(
                    "Invalid token!"
                );
            }

            $this->encodedAuthenticationTokenValidator->validate($token);

            $authenticationData = new AuthenticationData(
                $decodedToken->getUserId(),
                $decodedToken->getUsername(),
                $decodedToken->getUserPermissions(),
                $decodedToken->getUserSectors()
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
