<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Tests\Application\Services;

use Mvreisg\GamebaseBackend\Application\Services\Authentication\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\Authentication\Login\AuthenticationLoginInfo;
use Mvreisg\GamebaseBackend\Application\Services\Authentication\Login\AuthenticationLoginStates;
use Mvreisg\GamebaseBackend\Application\Services\Session\Login\SessionLoginInfo;
use Mvreisg\GamebaseBackend\Application\Services\Session\Login\SessionLoginStates;
use Mvreisg\GamebaseBackend\Application\Services\Session\SessionService;
use Mvreisg\GamebaseBackend\Application\Services\User\UserService;
use Mvreisg\GamebaseBackend\Domain\Authentication\Data\AuthenticationData;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Decoder\AuthenticationTokenDecoder;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Decoder\Exceptions\AuthenticationTokenDecoderException;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Encoder\AuthenticationTokenEncoder;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\State\Decoded\DecodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\State\Encoded\EncodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Cache\Token\Exceptions\TokenCacheException;
use Mvreisg\GamebaseBackend\Domain\Cache\Token\Interface\TokenCacheInterface;
use Mvreisg\GamebaseBackend\Domain\Encryption\Interface\EncryptionInterface;
use Mvreisg\GamebaseBackend\Domain\Entities\DecodedPassword;
use Mvreisg\GamebaseBackend\Domain\Entities\Exceptions\EntityException;
use Mvreisg\GamebaseBackend\Domain\Entities\Id;
use Mvreisg\GamebaseBackend\Domain\Entities\Name;
use Mvreisg\GamebaseBackend\Domain\Entities\Permission;
use Mvreisg\GamebaseBackend\Domain\Entities\PermissionValue;
use Mvreisg\GamebaseBackend\Domain\Entities\Sector;
use Mvreisg\GamebaseBackend\Domain\Entities\SectorValue;
use Mvreisg\GamebaseBackend\Domain\Entities\User;
use Mvreisg\GamebaseBackend\Domain\Entities\Username;
use Mvreisg\GamebaseBackend\Domain\Entities\UserSectorPermission;
use Mvreisg\GamebaseBackend\Domain\Entities\UserSectorPermissionCollection;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\UserRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\UserSectorPermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Session\Data\SessionData;
use Mvreisg\GamebaseBackend\Domain\Session\Exceptions\InvalidCredentialsException;
use Mvreisg\GamebaseBackend\Infrastructure\Cache\Mock\Clock\MockTokenCacheClock;
use Mvreisg\GamebaseBackend\Infrastructure\Cache\Mock\Token\MockTokenCache;
use PHPUnit\Framework\TestCase;

class SessionServiceTest extends TestCase
{
    public function testIfANewLoginValidForOneDayWithARegisteredUserWithoutPermissionsSucceds(): void
    {
        $id = Id::make(1);
        $username = Username::make("test");
        $password = DecodedPassword::make("test");
        $isActive = true;
        $user = $this->createMock(User::class);
        $user
            ->method("getIdValue")
            ->willReturn($id->getValue());
        $user
            ->method("getUsernameValue")
            ->willReturn($username->getValue());
        $user
            ->method("getPasswordValue")
            ->willReturn($password->getValue());
        $user
            ->method("getIsActive")
            ->willReturn($isActive);

        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository
            ->method("findByUsername")
            ->willReturn($user);

        $tokenCache = $this->createMock(TokenCacheInterface::class);
        $authenticationTokenDecoder = $this->createMock(AuthenticationTokenDecoder::class);
        $encrypter = $this->createMock(EncryptionInterface::class);
        $encrypter
            ->method("decrypt")
            ->willReturn("test");

        $userSectorPermissionCollection = $this->createMock(UserSectorPermissionCollection::class);
        $userSectorPermissionRepository = $this->createMock(UserSectorPermissionRepositoryInterface::class);
        $userSectorPermissionRepository
            ->method("findAllByUserId")
            ->willReturn($userSectorPermissionCollection);

        $token = $this->createMock(EncodedAuthenticationToken::class);
        $sessionData = $this->createMock(SessionData::class);
        $sessionData
            ->method("getUserId")
            ->willReturn($id);

        $sessionData
            ->method("getUsername")
            ->willReturn($username);

        $sessionData
            ->method("getUserSectorPermissionCollection")
            ->willReturn($userSectorPermissionCollection);

        $authenticationTokenEncoder = $this->createMock(AuthenticationTokenEncoder::class);
        $authenticationTokenEncoder
            ->method("encode")
            ->willReturn($token);

        $sessionService = new SessionService(
            $userRepository,
            $tokenCache,
            $encrypter,
            $authenticationTokenEncoder,
            $authenticationTokenDecoder,
            $userSectorPermissionRepository
        );

        $sessionLoginInfo = $this->createMock(SessionLoginInfo::class);
        $sessionLoginInfo
            ->method("getUsernameValue")
            ->willReturn($username->getValue());
        $sessionLoginInfo
            ->method("getPasswordValue")
            ->willReturn($password->getValue());
        $sessionLoginInfo
            ->method("getOneWeekLogin")
            ->willReturn(false);

        $result = $sessionService->tryLogin(
            $sessionLoginInfo
        );

        $this->assertEquals(
            $id,
            $result->getData()->getUserId()
        );
        $this->assertEquals(
            $username,
            $result->getData()->getUsername()
        );
        $this->assertEquals(
            $userSectorPermissionCollection,
            $result->getData()->getUserSectorPermissionCollection()
        );
        $this->assertEquals(
            SessionLoginStates::New,
            $result->getState()
        );
        $this->assertEquals(
            $token,
            $result->getToken()
        );
    }

    public function testIfANewLoginValidForOneDayWithARegisteredUserWithPermissionsToSectorsSucceds(): void
    {
        $id = Id::make(1);
        $username = Username::make("test");
        $password = DecodedPassword::make("test");
        $isActive = true;
        $user = $this->createMock(User::class);
        $user
            ->method("getIdValue")
            ->willReturn($id->getValue());
        $user
            ->method("getUsernameValue")
            ->willReturn($username->getValue());
        $user
            ->method("getPasswordValue")
            ->willReturn($password->getValue());
        $user
            ->method("getIsActive")
            ->willReturn($isActive);

        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository
            ->method("findByUsername")
            ->willReturn($user);

        $tokenCache = $this->createMock(TokenCacheInterface::class);
        $authenticationTokenDecoder = $this->createMock(AuthenticationTokenDecoder::class);
        $encrypter = $this->createMock(EncryptionInterface::class);
        $encrypter
            ->method("decrypt")
            ->willReturn("test");

        /**
         * @var UserSectorPermission[]
         */
        $userSectorPermissions = [];
        for ($i = 0; $i < 3; $i++) {
            $userSectorPermission = $this->createMock(UserSectorPermission::class);
            $userSectorPermission
                ->method("getIdValue")
                ->willReturn($i + 1);

            $userSectorPermission
                ->method("getUserIdValue")
                ->willReturn($i + 1);

            $userSectorPermission
                ->method("getSectorIdValue")
                ->willReturn($i + 1);

            $userSectorPermission
                ->method("getPermissionIdValue")
                ->willReturn($i + 1);

            $userSectorPermissions[$i] = $userSectorPermission;
        }

        $userSectorPermissionCollection = $this->createMock(UserSectorPermissionCollection::class);
        $userSectorPermissionCollection
            ->method("fetchAll")
            ->willReturn($userSectorPermissions);

        $userSectorPermissionRepository = $this->createMock(UserSectorPermissionRepositoryInterface::class);
        $userSectorPermissionRepository
            ->method("findAllByUserId")
            ->willReturn($userSectorPermissionCollection);

        $token = $this->createMock(EncodedAuthenticationToken::class);
        $sessionData = $this->createMock(SessionData::class);
        $sessionData
            ->method("getUserId")
            ->willReturn($id);

        $sessionData
            ->method("getUsername")
            ->willReturn($username);

        $sessionData
            ->method("getUserSectorPermissionCollection")
            ->willReturn($userSectorPermissionCollection);

        $authenticationTokenEncoder = $this->createMock(AuthenticationTokenEncoder::class);
        $authenticationTokenEncoder
            ->method("encode")
            ->willReturn($token);

        $sessionService = new SessionService(
            $userRepository,
            $tokenCache,
            $encrypter,
            $authenticationTokenEncoder,
            $authenticationTokenDecoder,
            $userSectorPermissionRepository
        );

        $sessionLoginInfo = $this->createMock(SessionLoginInfo::class);
        $sessionLoginInfo
            ->method("getUsernameValue")
            ->willReturn($username->getValue());

        $sessionLoginInfo
            ->method("getPasswordValue")
            ->willReturn($password->getValue());
        $sessionLoginInfo
            ->method("getOneWeekLogin")
            ->willReturn(false);

        $result = $sessionService->tryLogin(
            $sessionLoginInfo
        );

        $this->assertEquals(
            $id,
            $result->getData()->getUserId()
        );
        $this->assertEquals(
            $username,
            $result->getData()->getUsername()
        );
        $this->assertEquals(
            $userSectorPermissionCollection,
            $result->getData()->getUserSectorPermissionCollection()
        );
        $this->assertEquals(
            SessionLoginStates::New,
            $result->getState()
        );
        $this->assertEquals(
            $token,
            $result->getToken()
        );
    }

    public function testIfANewLoginValidForOneWeekWithARegisteredUserWithoutPermissionsSucceds(): void
    {
        $id = Id::make(1);
        $username = Username::make("test");
        $password = DecodedPassword::make("test");
        $isActive = true;
        $user = $this->createMock(User::class);
        $user
            ->method("getIdValue")
            ->willReturn($id->getValue());
        $user
            ->method("getUsernameValue")
            ->willReturn($username->getValue());
        $user
            ->method("getPasswordValue")
            ->willReturn($password->getValue());
        $user
            ->method("getIsActive")
            ->willReturn($isActive);

        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository
            ->method("findByUsername")
            ->willReturn($user);

        $tokenCache = $this->createMock(TokenCacheInterface::class);
        $authenticationTokenDecoder = $this->createMock(AuthenticationTokenDecoder::class);
        $encrypter = $this->createMock(EncryptionInterface::class);
        $encrypter
            ->method("decrypt")
            ->willReturn("test");

        $userSectorPermissionCollection = $this->createMock(UserSectorPermissionCollection::class);
        $userSectorPermissionRepository = $this->createMock(UserSectorPermissionRepositoryInterface::class);
        $userSectorPermissionRepository
            ->method("findAllByUserId")
            ->willReturn($userSectorPermissionCollection);

        $token = $this->createMock(EncodedAuthenticationToken::class);
        $sessionData = $this->createMock(SessionData::class);
        $sessionData
            ->method("getUserId")
            ->willReturn($id);

        $sessionData
            ->method("getUsername")
            ->willReturn($username);

        $sessionData
            ->method("getUserSectorPermissionCollection")
            ->willReturn($userSectorPermissionCollection);

        $authenticationTokenEncoder = $this->createMock(AuthenticationTokenEncoder::class);
        $authenticationTokenEncoder
            ->method("encode")
            ->willReturn($token);

        $sessionService = new SessionService(
            $userRepository,
            $tokenCache,
            $encrypter,
            $authenticationTokenEncoder,
            $authenticationTokenDecoder,
            $userSectorPermissionRepository
        );

        $sessionLoginInfo = $this->createMock(SessionLoginInfo::class);
        $sessionLoginInfo
            ->method("getUsernameValue")
            ->willReturn($username->getValue());
        $sessionLoginInfo
            ->method("getPasswordValue")
            ->willReturn($password->getValue());
        $sessionLoginInfo
            ->method("getOneWeekLogin")
            ->willReturn(true);

        $result = $sessionService->tryLogin(
            $sessionLoginInfo
        );

        $this->assertEquals(
            $id,
            $result->getData()->getUserId()
        );
        $this->assertEquals(
            $username,
            $result->getData()->getUsername()
        );
        $this->assertEquals(
            $userSectorPermissionCollection,
            $result->getData()->getUserSectorPermissionCollection()
        );
        $this->assertEquals(
            SessionLoginStates::New,
            $result->getState()
        );
        $this->assertEquals(
            $token,
            $result->getToken()
        );
    }

    public function testIfANewLoginValidForOneWeekWithARegisteredUserWithPermissionToSectorsSucceds(): void
    {
        $id = Id::make(1);
        $username = Username::make("test");
        $password = DecodedPassword::make("test");
        $isActive = true;
        $user = $this->createMock(User::class);
        $user
            ->method("getIdValue")
            ->willReturn($id->getValue());
        $user
            ->method("getUsernameValue")
            ->willReturn($username->getValue());
        $user
            ->method("getPasswordValue")
            ->willReturn($password->getValue());
        $user
            ->method("getIsActive")
            ->willReturn($isActive);

        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository
            ->method("findByUsername")
            ->willReturn($user);

        $tokenCache = $this->createMock(TokenCacheInterface::class);
        $authenticationTokenDecoder = $this->createMock(AuthenticationTokenDecoder::class);
        $encrypter = $this->createMock(EncryptionInterface::class);
        $encrypter
            ->method("decrypt")
            ->willReturn("test");

        /**
         * @var UserSectorPermission[]
         */
        $userSectorPermissions = [];
        for ($i = 0; $i < 3; $i++) {
            $userSectorPermission = $this->createMock(UserSectorPermission::class);
            $userSectorPermission
                ->method("getIdValue")
                ->willReturn($i + 1);

            $userSectorPermission
                ->method("getUserIdValue")
                ->willReturn($i + 1);

            $userSectorPermission
                ->method("getSectorIdValue")
                ->willReturn($i + 1);

            $userSectorPermission
                ->method("getPermissionIdValue")
                ->willReturn($i + 1);

            $userSectorPermissions[$i] = $userSectorPermission;
        }

        $userSectorPermissionCollection = $this->createMock(UserSectorPermissionCollection::class);
        $userSectorPermissionCollection
            ->method("fetchAll")
            ->willReturn($userSectorPermissions);

        $userSectorPermissionRepository = $this->createMock(UserSectorPermissionRepositoryInterface::class);
        $userSectorPermissionRepository
            ->method("findAllByUserId")
            ->willReturn($userSectorPermissionCollection);

        $token = $this->createMock(EncodedAuthenticationToken::class);
        $sessionData = $this->createMock(SessionData::class);
        $sessionData
            ->method("getUserId")
            ->willReturn($id);

        $sessionData
            ->method("getUsername")
            ->willReturn($username);

        $sessionData
            ->method("getUserSectorPermissionCollection")
            ->willReturn($userSectorPermissionCollection);

        $authenticationTokenEncoder = $this->createMock(AuthenticationTokenEncoder::class);
        $authenticationTokenEncoder
            ->method("encode")
            ->willReturn($token);

        $sessionService = new SessionService(
            $userRepository,
            $tokenCache,
            $encrypter,
            $authenticationTokenEncoder,
            $authenticationTokenDecoder,
            $userSectorPermissionRepository
        );

        $sessionLoginInfo = $this->createMock(SessionLoginInfo::class);
        $sessionLoginInfo
            ->method("getUsernameValue")
            ->willReturn($username->getValue());

        $sessionLoginInfo
            ->method("getPasswordValue")
            ->willReturn($password->getValue());
        $sessionLoginInfo
            ->method("getOneWeekLogin")
            ->willReturn(true);

        $result = $sessionService->tryLogin(
            $sessionLoginInfo
        );

        $this->assertEquals(
            $id,
            $result->getData()->getUserId()
        );
        $this->assertEquals(
            $username,
            $result->getData()->getUsername()
        );
        $this->assertEquals(
            $userSectorPermissionCollection,
            $result->getData()->getUserSectorPermissionCollection()
        );
        $this->assertEquals(
            SessionLoginStates::New,
            $result->getState()
        );
        $this->assertEquals(
            $token,
            $result->getToken()
        );
    }

    public function testIfANewLoginValidForOneDayWithARegisteredUserWithPermissionsToSectorsButWithAInvalidUsernameFails(): void
    {
        $this->expectException(EntityException::class);

        Username::make("-");
    }

    public function testIfANewLoginValidForOneWeekWithARegisteredUserWithPermissionsToSectorsButWithAInvalidUsernameFails(): void
    {
        $this->expectException(EntityException::class);

        Username::make("-");
    }

    public function testIfANewLoginValidForOneDayWithARegisteredUserWithPermissionsToSectorsButWithAInvalidPasswordFails(): void
    {
        $this->expectException(EntityException::class);

        DecodedPassword::make("-");
    }

    public function testIfANewLoginValidForOneWeekWithARegisteredUserWithPermissionsToSectorsButWithAInvalidPasswordFails(): void
    {
        $this->expectException(EntityException::class);

        DecodedPassword::make("-");
    }

    public function testIfANewLoginValidForOneDayWithInvalidCredentialsFails(): void
    {
        $id = Id::make(1);
        $username = Username::make("test");
        $password = DecodedPassword::make("test");
        $isActive = true;
        $user = $this->createMock(User::class);
        $user
            ->method("getIdValue")
            ->willReturn($id->getValue());
        $user
            ->method("getUsernameValue")
            ->willReturn($username->getValue());
        $user
            ->method("getPasswordValue")
            ->willReturn($password->getValue());
        $user
            ->method("getIsActive")
            ->willReturn($isActive);

        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository
            ->method("findByUsername")
            ->willReturn($user);

        $tokenCache = $this->createMock(TokenCacheInterface::class);
        $authenticationTokenDecoder = $this->createMock(AuthenticationTokenDecoder::class);
        $encrypter = $this->createMock(EncryptionInterface::class);
        $encrypter
            ->method("decrypt")
            ->willReturn("test");

        /**
         * @var UserSectorPermission[]
         */
        $userSectorPermissions = [];
        for ($i = 0; $i < 3; $i++) {
            $userSectorPermission = $this->createMock(UserSectorPermission::class);
            $userSectorPermission
                ->method("getIdValue")
                ->willReturn($i + 1);

            $userSectorPermission
                ->method("getUserIdValue")
                ->willReturn($i + 1);

            $userSectorPermission
                ->method("getSectorIdValue")
                ->willReturn($i + 1);

            $userSectorPermission
                ->method("getPermissionIdValue")
                ->willReturn($i + 1);

            $userSectorPermissions[$i] = $userSectorPermission;
        }

        $userSectorPermissionCollection = $this->createMock(UserSectorPermissionCollection::class);
        $userSectorPermissionCollection
            ->method("fetchAll")
            ->willReturn($userSectorPermissions);

        $userSectorPermissionRepository = $this->createMock(UserSectorPermissionRepositoryInterface::class);
        $userSectorPermissionRepository
            ->method("findAllByUserId")
            ->willReturn($userSectorPermissionCollection);

        $token = $this->createMock(EncodedAuthenticationToken::class);
        $sessionData = $this->createMock(SessionData::class);
        $sessionData
            ->method("getUserId")
            ->willReturn($id);

        $sessionData
            ->method("getUsername")
            ->willReturn($username);

        $sessionData
            ->method("getUserSectorPermissionCollection")
            ->willReturn($userSectorPermissionCollection);

        $authenticationTokenEncoder = $this->createMock(AuthenticationTokenEncoder::class);
        $authenticationTokenEncoder
            ->method("encode")
            ->willReturn($token);

        $sessionService = new SessionService(
            $userRepository,
            $tokenCache,
            $encrypter,
            $authenticationTokenEncoder,
            $authenticationTokenDecoder,
            $userSectorPermissionRepository
        );

        $sessionLoginInfo = $this->createMock(SessionLoginInfo::class);
        $sessionLoginInfo
            ->method("getUsernameValue")
            ->willReturn("error");
        $sessionLoginInfo
            ->method("getPasswordValue")
            ->willReturn("error");
        $sessionLoginInfo
            ->method("getOneWeekLogin")
            ->willReturn(false);

        $this->expectException(InvalidCredentialsException::class);

        $sessionService->tryLogin(
            $sessionLoginInfo
        );
    }

    public function testIfANewLoginValidForOneWeekWithInvalidCredentialsFails(): void
    {
        $id = Id::make(1);
        $username = Username::make("test");
        $password = DecodedPassword::make("test");
        $isActive = true;
        $user = $this->createMock(User::class);
        $user
            ->method("getIdValue")
            ->willReturn($id->getValue());
        $user
            ->method("getUsernameValue")
            ->willReturn($username->getValue());
        $user
            ->method("getPasswordValue")
            ->willReturn($password->getValue());
        $user
            ->method("getIsActive")
            ->willReturn($isActive);

        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository
            ->method("findByUsername")
            ->willReturn($user);

        $tokenCache = $this->createMock(TokenCacheInterface::class);
        $authenticationTokenDecoder = $this->createMock(AuthenticationTokenDecoder::class);
        $encrypter = $this->createMock(EncryptionInterface::class);
        $encrypter
            ->method("decrypt")
            ->willReturn("test");

        /**
         * @var UserSectorPermission[]
         */
        $userSectorPermissions = [];
        for ($i = 0; $i < 3; $i++) {
            $userSectorPermission = $this->createMock(UserSectorPermission::class);
            $userSectorPermission
                ->method("getIdValue")
                ->willReturn($i + 1);

            $userSectorPermission
                ->method("getUserIdValue")
                ->willReturn($i + 1);

            $userSectorPermission
                ->method("getSectorIdValue")
                ->willReturn($i + 1);

            $userSectorPermission
                ->method("getPermissionIdValue")
                ->willReturn($i + 1);

            $userSectorPermissions[$i] = $userSectorPermission;
        }

        $userSectorPermissionCollection = $this->createMock(UserSectorPermissionCollection::class);
        $userSectorPermissionCollection
            ->method("fetchAll")
            ->willReturn($userSectorPermissions);

        $userSectorPermissionRepository = $this->createMock(UserSectorPermissionRepositoryInterface::class);
        $userSectorPermissionRepository
            ->method("findAllByUserId")
            ->willReturn($userSectorPermissionCollection);

        $token = $this->createMock(EncodedAuthenticationToken::class);
        $sessionData = $this->createMock(SessionData::class);
        $sessionData
            ->method("getUserId")
            ->willReturn($id);

        $sessionData
            ->method("getUsername")
            ->willReturn($username);

        $sessionData
            ->method("getUserSectorPermissionCollection")
            ->willReturn($userSectorPermissionCollection);

        $authenticationTokenEncoder = $this->createMock(AuthenticationTokenEncoder::class);
        $authenticationTokenEncoder
            ->method("encode")
            ->willReturn($token);

        $sessionService = new SessionService(
            $userRepository,
            $tokenCache,
            $encrypter,
            $authenticationTokenEncoder,
            $authenticationTokenDecoder,
            $userSectorPermissionRepository
        );

        $sessionLoginInfo = $this->createMock(SessionLoginInfo::class);
        $sessionLoginInfo
            ->method("getUsernameValue")
            ->willReturn("error");
        $sessionLoginInfo
            ->method("getPasswordValue")
            ->willReturn("error");
        $sessionLoginInfo
            ->method("getOneWeekLogin")
            ->willReturn(true);

        $this->expectException(InvalidCredentialsException::class);

        $sessionService->tryLogin(
            $sessionLoginInfo
        );
    }

    public function testIfAExistantLoginValidForOneDayWithARegisteredUserWithPermissionsToSectorsSucceds(): void
    {
        $id = Id::make(1);
        $username = Username::make("test");
        $password = DecodedPassword::make("test");
        $isActive = true;
        $user = $this->createMock(User::class);
        $user
            ->method("getIdValue")
            ->willReturn($id->getValue());
        $user
            ->method("getUsernameValue")
            ->willReturn($username->getValue());
        $user
            ->method("getPasswordValue")
            ->willReturn($password->getValue());
        $user
            ->method("getIsActive")
            ->willReturn($isActive);

        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository
            ->method("findByUsername")
            ->willReturn($user);

        $tokenCache = new MockTokenCache(
            new MockTokenCacheClock(
                new \DateTimeImmutable(),
                new \DateTimeZone("UTC")
            )
        );

        $decodedToken = $this->createMock(DecodedAuthenticationToken::class);

        $decodedToken
            ->method("getUserId")
            ->willReturn($id);

        $decodedToken
            ->method("getUsername")
            ->willReturn($username);

        $decodedToken
            ->method("getIssuedAt")
            ->willReturn(new \DateTimeImmutable());

        $decodedToken
            ->method("getExpiresAt")
            ->willReturn(
                new \DateTimeImmutable()
                    ->add(
                        new \DateInterval("P1D")
                    )
            );

        $authenticationTokenDecoder = $this->createMock(AuthenticationTokenDecoder::class);
        $authenticationTokenDecoder
            ->method("decode")
            ->willReturn($decodedToken);

        $encrypter = $this->createMock(EncryptionInterface::class);
        $encrypter
            ->method("decrypt")
            ->willReturn("test");

        /**
         * @var UserSectorPermission[]
         */
        $userSectorPermissions = [];
        for ($i = 0; $i < 3; $i++) {
            $userSectorPermission = $this->createMock(UserSectorPermission::class);
            $userSectorPermission
                ->method("getIdValue")
                ->willReturn($i + 1);

            $userSectorPermission
                ->method("getUserIdValue")
                ->willReturn($i + 1);

            $userSectorPermission
                ->method("getSectorIdValue")
                ->willReturn($i + 1);

            $userSectorPermission
                ->method("getPermissionIdValue")
                ->willReturn($i + 1);

            $userSectorPermissions[$i] = $userSectorPermission;
        }

        $userSectorPermissionCollection = $this->createMock(UserSectorPermissionCollection::class);
        $userSectorPermissionCollection
            ->method("fetchAll")
            ->willReturn($userSectorPermissions);

        $userSectorPermissionRepository = $this->createMock(UserSectorPermissionRepositoryInterface::class);
        $userSectorPermissionRepository
            ->method("findAllByUserId")
            ->willReturn($userSectorPermissionCollection);

        $token = $this->createMock(EncodedAuthenticationToken::class);
        $sessionData = $this->createMock(SessionData::class);
        $sessionData
            ->method("getUserId")
            ->willReturn($id);

        $sessionData
            ->method("getUsername")
            ->willReturn($username);

        $sessionData
            ->method("getUserSectorPermissionCollection")
            ->willReturn($userSectorPermissionCollection);

        $authenticationTokenEncoder = $this->createMock(AuthenticationTokenEncoder::class);
        $authenticationTokenEncoder
            ->method("encode")
            ->willReturn($token);

        $sessionService = new SessionService(
            $userRepository,
            $tokenCache,
            $encrypter,
            $authenticationTokenEncoder,
            $authenticationTokenDecoder,
            $userSectorPermissionRepository
        );

        $sessionLoginInfo = $this->createMock(SessionLoginInfo::class);
        $sessionLoginInfo
            ->method("getUsernameValue")
            ->willReturn($username->getValue());

        $sessionLoginInfo
            ->method("getPasswordValue")
            ->willReturn($password->getValue());
        $sessionLoginInfo
            ->method("getOneWeekLogin")
            ->willReturn(false);

        $result = $sessionService->tryLogin(
            $sessionLoginInfo
        );

        $this->assertEquals(
            $id,
            $result->getData()->getUserId()
        );
        $this->assertEquals(
            $username,
            $result->getData()->getUsername()
        );
        $this->assertEquals(
            $userSectorPermissionCollection,
            $result->getData()->getUserSectorPermissionCollection()
        );
        $this->assertEquals(
            SessionLoginStates::New,
            $result->getState()
        );
        $this->assertEquals(
            $token,
            $result->getToken()
        );

        $secondResult = $sessionService->tryLogin(
            $sessionLoginInfo
        );

        $this->assertEquals(
            $id,
            $secondResult->getData()->getUserId()
        );
        $this->assertEquals(
            $username,
            $secondResult->getData()->getUsername()
        );
        $this->assertEquals(
            $userSectorPermissionCollection,
            $secondResult->getData()->getUserSectorPermissionCollection()
        );
        $this->assertEquals(
            SessionLoginStates::Existing,
            $secondResult->getState()
        );
        $this->assertEquals(
            $token,
            $secondResult->getToken()
        );
    }
    /*
    public function testIfAExistantLoginValidForOneWeekWithARegisteredUserWithPermissionToSectorsSucceds(): void
    {
        $insertedPermission = $this->permissionRepository->insert(
            new Permission(
                Name::make("permission"),
                PermissionValue::make("a"),
                true
            )
        );

        $insertedSector = $this->sectorRepository->insert(
            new Sector(
                Name::make("sector"),
                SectorValue::make("a"),
                true
            )
        );

        $username = Username::make("test");
        $password = DecodedPassword::make("test");
        $isActive = true;
        $insertedUser = $this->userService->insert(
            new User(
                $username,
                $password,
                $isActive
            )
        );

        $insertedUserSectorPermission = $this->userSectorPermissionRepository->insert(
            new UserSectorPermission(
                Id::make($insertedUser->getIdValue()),
                Id::make($insertedSector->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $oneWeekLogin = true;
        $newResult = $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                $password,
                $oneWeekLogin
            )
        );

        $this->assertEquals(AuthenticationLoginStates::New, $newResult->getState());
        $this->assertNotEmpty($newResult->getToken());
        $this->assertEquals(
            new AuthenticationData(
                Id::make($insertedUser->getIdValue()),
                Username::make($insertedUser->getUsernameValue()),
                new UserSectorPermissionCollection([
                    $insertedUserSectorPermission
                ])
            ),
            $newResult->getData()
        );

        $existingResult = $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                $password,
                $oneWeekLogin
            )
        );

        $this->assertEquals(AuthenticationLoginStates::Existing, $existingResult->getState());
        $this->assertNotEmpty($existingResult->getToken());
        $this->assertEquals(
            new AuthenticationData(
                Id::make($insertedUser->getIdValue()),
                Username::make($insertedUser->getUsernameValue()),
                new UserSectorPermissionCollection([
                    $insertedUserSectorPermission
                ])
            ),
            $existingResult->getData()
        );
    }

    public function testIfAExistantLoginValidForOneDayWithARegisteredUserWithPermissionsToSectorsButWithInvalidUsernameFails(): void
    {
        $insertedPermission = $this->permissionRepository->insert(
            new Permission(
                Name::make("permission"),
                PermissionValue::make("a"),
                true
            )
        );

        $insertedSector = $this->sectorRepository->insert(
            new Sector(
                Name::make("sector"),
                SectorValue::make("a"),
                true
            )
        );

        $username = Username::make("test");
        $password = DecodedPassword::make("test");
        $isActive = true;
        $insertedUser = $this->userService->insert(
            new User(
                $username,
                $password,
                $isActive
            )
        );

        $insertedUserSectorPermission = $this->userSectorPermissionRepository->insert(
            new UserSectorPermission(
                Id::make($insertedUser->getIdValue()),
                Id::make($insertedSector->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $oneWeekLogin = false;
        $newResult = $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                $password,
                $oneWeekLogin
            )
        );

        $this->assertEquals(AuthenticationLoginStates::New, $newResult->getState());
        $this->assertNotEmpty($newResult->getToken());
        $this->assertEquals(
            new AuthenticationData(
                Id::make($insertedUser->getIdValue()),
                Username::make($insertedUser->getUsernameValue()),
                new UserSectorPermissionCollection([
                    $insertedUserSectorPermission
                ])
            ),
            $newResult->getData()
        );

        $this->expectException(EntityException::class);
        $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                Username::make("-"),
                $password,
                $oneWeekLogin
            )
        );
    }

    public function testIfAExistantLoginValidForOneWeekWithARegisteredUserWithPermissionsToSectorsButWithAInvalidUsernameFails(): void
    {
        $insertedPermission = $this->permissionRepository->insert(
            new Permission(
                Name::make("permission"),
                PermissionValue::make("a"),
                true
            )
        );

        $insertedSector = $this->sectorRepository->insert(
            new Sector(
                Name::make("sector"),
                SectorValue::make("a"),
                true
            )
        );

        $username = Username::make("test");
        $password = DecodedPassword::make("test");
        $isActive = true;
        $insertedUser = $this->userService->insert(
            new User(
                $username,
                $password,
                $isActive
            )
        );

        $insertedUserSectorPermission = $this->userSectorPermissionRepository->insert(
            new UserSectorPermission(
                Id::make($insertedUser->getIdValue()),
                Id::make($insertedSector->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $oneWeekLogin = true;
        $newResult = $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                $password,
                $oneWeekLogin
            )
        );

        $this->assertEquals(AuthenticationLoginStates::New, $newResult->getState());
        $this->assertNotEmpty($newResult->getToken());
        $this->assertEquals(
            new AuthenticationData(
                Id::make($insertedUser->getIdValue()),
                Username::make($insertedUser->getUsernameValue()),
                new UserSectorPermissionCollection([
                    $insertedUserSectorPermission
                ])
            ),
            $newResult->getData()
        );

        $this->expectException(EntityException::class);
        $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                Username::make("-"),
                $password,
                $oneWeekLogin
            )
        );
    }

    public function testIfAExistantLoginValidForOneDayWithARegisteredUserWithPermissionsToSectorsButWithAInvalidPasswordFails(): void
    {
        $insertedPermission = $this->permissionRepository->insert(
            new Permission(
                Name::make("permission"),
                PermissionValue::make("a"),
                true
            )
        );

        $insertedSector = $this->sectorRepository->insert(
            new Sector(
                Name::make("sector"),
                SectorValue::make("a"),
                true
            )
        );

        $username = Username::make("test");
        $password = DecodedPassword::make("test");
        $isActive = true;
        $insertedUser = $this->userService->insert(
            new User(
                $username,
                $password,
                $isActive
            )
        );

        $insertedUserSectorPermission = $this->userSectorPermissionRepository->insert(
            new UserSectorPermission(
                Id::make($insertedUser->getIdValue()),
                Id::make($insertedSector->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $oneWeekLogin = false;
        $newResult = $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                $password,
                $oneWeekLogin
            )
        );

        $this->assertEquals(AuthenticationLoginStates::New, $newResult->getState());
        $this->assertNotEmpty($newResult->getToken());
        $this->assertEquals(
            new AuthenticationData(
                Id::make($insertedUser->getIdValue()),
                Username::make($insertedUser->getUsernameValue()),
                new UserSectorPermissionCollection([
                    $insertedUserSectorPermission
                ])
            ),
            $newResult->getData()
        );

        $this->expectException(EntityException::class);
        $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                DecodedPassword::make("-"),
                $oneWeekLogin
            )
        );
    }

    public function testIfAExistantLoginValidForOneWeekWithARegisteredUserWithPermissionsToSectorsButWithAInvalidPasswordFails(): void
    {
        $insertedPermission = $this->permissionRepository->insert(
            new Permission(
                Name::make("permission"),
                PermissionValue::make("a"),
                true
            )
        );

        $insertedSector = $this->sectorRepository->insert(
            new Sector(
                Name::make("sector"),
                SectorValue::make("a"),
                true
            )
        );

        $username = Username::make("test");
        $password = DecodedPassword::make("test");
        $isActive = true;
        $insertedUser = $this->userService->insert(
            new User(
                $username,
                $password,
                $isActive
            )
        );

        $insertedUserSectorPermission = $this->userSectorPermissionRepository->insert(
            new UserSectorPermission(
                Id::make($insertedUser->getIdValue()),
                Id::make($insertedSector->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $oneWeekLogin = true;
        $newResult = $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                $password,
                $oneWeekLogin
            )
        );

        $this->assertEquals(AuthenticationLoginStates::New, $newResult->getState());
        $this->assertNotEmpty($newResult->getToken());
        $this->assertEquals(
            new AuthenticationData(
                Id::make($insertedUser->getIdValue()),
                Username::make($insertedUser->getUsernameValue()),
                new UserSectorPermissionCollection([
                    $insertedUserSectorPermission
                ])
            ),
            $newResult->getData()
        );

        $this->expectException(EntityException::class);
        $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                DecodedPassword::make("-"),
                $oneWeekLogin
            )
        );
    }

    public function testIfAOneDayExistantLoginByAUserWithPermissionsToSectorsDoesNotLoginAfterOneDayBecauseOfExpiredTokenOnCache(): void
    {
        $insertedPermission = $this->permissionRepository->insert(
            new Permission(
                Name::make("permission"),
                PermissionValue::make("a"),
                true
            )
        );

        $insertedSector = $this->sectorRepository->insert(
            new Sector(
                Name::make("sector"),
                SectorValue::make("a"),
                true
            )
        );

        $username = Username::make("test");
        $password = DecodedPassword::make("test");
        $isActive = true;
        $insertedUser = $this->userService->insert(
            new User(
                $username,
                $password,
                $isActive
            )
        );

        $insertedUserSectorPermission = $this->userSectorPermissionRepository->insert(
            new UserSectorPermission(
                Id::make($insertedUser->getIdValue()),
                Id::make($insertedSector->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $oneWeekLogin = false;
        $newResult = $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                $password,
                $oneWeekLogin
            )
        );

        $this->assertEquals(AuthenticationLoginStates::New, $newResult->getState());
        $this->assertNotEmpty($newResult->getToken());
        $this->assertEquals(
            new AuthenticationData(
                Id::make($insertedUser->getIdValue()),
                Username::make($insertedUser->getUsernameValue()),
                new UserSectorPermissionCollection([
                    $insertedUserSectorPermission
                ])
            ),
            $newResult->getData()
        );

        $interval = new \DateInterval("P1D");

        $this->expectException(TokenCacheException::class);

        $this->tokenCacheClock->advance(
            $interval
        );

        $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                $password,
                $oneWeekLogin
            )
        );
    }

    public function testIfAOneDayExistantLoginByAUserWithPermissionsToSectorsDoesNotLoginAfterOneDayBecauseOfExpiredTokenWhenValidating(): void
    {
        $insertedPermission = $this->permissionRepository->insert(
            new Permission(
                Name::make("permission"),
                PermissionValue::make("a"),
                true
            )
        );

        $insertedSector = $this->sectorRepository->insert(
            new Sector(
                Name::make("sector"),
                SectorValue::make("a"),
                true
            )
        );

        $username = Username::make("test");
        $password = DecodedPassword::make("test");
        $isActive = true;
        $insertedUser = $this->userService->insert(
            new User(
                $username,
                $password,
                $isActive
            )
        );

        $insertedUserSectorPermission = $this->userSectorPermissionRepository->insert(
            new UserSectorPermission(
                Id::make($insertedUser->getIdValue()),
                Id::make($insertedSector->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $oneWeekLogin = false;
        $newResult = $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                $password,
                $oneWeekLogin
            )
        );

        $this->assertEquals(AuthenticationLoginStates::New, $newResult->getState());
        $this->assertNotEmpty($newResult->getToken());
        $this->assertEquals(
            new AuthenticationData(
                Id::make($insertedUser->getIdValue()),
                Username::make($insertedUser->getUsernameValue()),
                new UserSectorPermissionCollection([
                    $insertedUserSectorPermission
                ])
            ),
            $newResult->getData()
        );

        $interval = new \DateInterval("P1D");

        $this->expectException(AuthenticationTokenDecoderException::class);

        $this->authenticationTokenClock->advance(
            $interval
        );

        $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                $password,
                $oneWeekLogin
            )
        );
    }

    public function testIfAOneDayExistantLoginByAUserWithPermissionsToSectorsDoesNotLoginAfterOneWeekBecauseOfExpiredTokenOnCache(): void
    {
        $insertedPermission = $this->permissionRepository->insert(
            new Permission(
                Name::make("permission"),
                PermissionValue::make("a"),
                true
            )
        );

        $insertedSector = $this->sectorRepository->insert(
            new Sector(
                Name::make("sector"),
                SectorValue::make("a"),
                true
            )
        );

        $username = Username::make("test");
        $password = DecodedPassword::make("test");
        $isActive = true;
        $insertedUser = $this->userService->insert(
            new User(
                $username,
                $password,
                $isActive
            )
        );

        $insertedUserSectorPermission = $this->userSectorPermissionRepository->insert(
            new UserSectorPermission(
                Id::make($insertedUser->getIdValue()),
                Id::make($insertedSector->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $oneWeekLogin = false;
        $newResult = $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                $password,
                $oneWeekLogin
            )
        );

        $this->assertEquals(AuthenticationLoginStates::New, $newResult->getState());
        $this->assertNotEmpty($newResult->getToken());
        $this->assertEquals(
            new AuthenticationData(
                Id::make($insertedUser->getIdValue()),
                Username::make($insertedUser->getUsernameValue()),
                new UserSectorPermissionCollection([
                    $insertedUserSectorPermission
                ])
            ),
            $newResult->getData()
        );

        $interval = new \DateInterval("P7D");

        $this->expectException(TokenCacheException::class);

        $this->tokenCacheClock->advance(
            $interval
        );

        $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                $password,
                $oneWeekLogin
            )
        );
    }

    public function testIfAOneDayExistantLoginByAUserWithPermissionsToSectorsDoesNotLoginAfterOneWeekBecauseOfExpiredTokenWhenValidating(): void
    {
        $insertedPermission = $this->permissionRepository->insert(
            new Permission(
                Name::make("permission"),
                PermissionValue::make("a"),
                true
            )
        );

        $insertedSector = $this->sectorRepository->insert(
            new Sector(
                Name::make("sector"),
                SectorValue::make("a"),
                true
            )
        );

        $username = Username::make("test");
        $password = DecodedPassword::make("test");
        $isActive = true;
        $insertedUser = $this->userService->insert(
            new User(
                $username,
                $password,
                $isActive
            )
        );

        $insertedUserSectorPermission = $this->userSectorPermissionRepository->insert(
            new UserSectorPermission(
                Id::make($insertedUser->getIdValue()),
                Id::make($insertedSector->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $oneWeekLogin = false;
        $newResult = $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                $password,
                $oneWeekLogin
            )
        );

        $this->assertEquals(AuthenticationLoginStates::New, $newResult->getState());
        $this->assertNotEmpty($newResult->getToken());
        $this->assertEquals(
            new AuthenticationData(
                Id::make($insertedUser->getIdValue()),
                Username::make($insertedUser->getUsernameValue()),
                new UserSectorPermissionCollection([
                    $insertedUserSectorPermission
                ])
            ),
            $newResult->getData()
        );

        $interval = new \DateInterval("P7D");

        $this->expectException(AuthenticationTokenDecoderException::class);

        $this->authenticationTokenClock->advance(
            $interval
        );

        $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                $password,
                $oneWeekLogin
            )
        );
    }

    public function testIfAOneWeekExistantLoginByAUserWithPermissionsToSectorsDoesLoginAfterOneDay(): void
    {
        $insertedPermission = $this->permissionRepository->insert(
            new Permission(
                Name::make("permission"),
                PermissionValue::make("a"),
                true
            )
        );

        $insertedSector = $this->sectorRepository->insert(
            new Sector(
                Name::make("sector"),
                SectorValue::make("a"),
                true
            )
        );

        $username = Username::make("test");
        $password = DecodedPassword::make("test");
        $isActive = true;
        $insertedUser = $this->userService->insert(
            new User(
                $username,
                $password,
                $isActive
            )
        );

        $insertedUserSectorPermission = $this->userSectorPermissionRepository->insert(
            new UserSectorPermission(
                Id::make($insertedUser->getIdValue()),
                Id::make($insertedSector->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $oneWeekLogin = true;
        $newResult = $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                $password,
                $oneWeekLogin
            )
        );

        $this->assertEquals(AuthenticationLoginStates::New, $newResult->getState());
        $this->assertNotEmpty($newResult->getToken());
        $this->assertEquals(
            new AuthenticationData(
                Id::make($insertedUser->getIdValue()),
                Username::make($insertedUser->getUsernameValue()),
                new UserSectorPermissionCollection([
                    $insertedUserSectorPermission
                ])
            ),
            $newResult->getData()
        );

        $interval = new \DateInterval("P1D");

        $this->tokenCacheClock->advance(
            $interval
        );
        $this->authenticationTokenClock->advance(
            $interval
        );

        $existingResult = $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                $password,
                $oneWeekLogin
            )
        );

        $this->assertEquals(AuthenticationLoginStates::Existing, $existingResult->getState());
        $this->assertNotEmpty($existingResult->getToken());
        $this->assertEquals(
            new AuthenticationData(
                Id::make($insertedUser->getIdValue()),
                Username::make($insertedUser->getUsernameValue()),
                new UserSectorPermissionCollection([
                    $insertedUserSectorPermission
                ])
            ),
            $existingResult->getData()
        );
    }

    public function testIfAOneWeekExistantLoginByAUserWithPermissionsToSectorsDoesNotLoginAfterOneWeekBecauseOfExpiredTokenOnCache(): void
    {
        $insertedPermission = $this->permissionRepository->insert(
            new Permission(
                Name::make("permission"),
                PermissionValue::make("a"),
                true
            )
        );

        $insertedSector = $this->sectorRepository->insert(
            new Sector(
                Name::make("sector"),
                SectorValue::make("a"),
                true
            )
        );

        $username = Username::make("test");
        $password = DecodedPassword::make("test");
        $isActive = true;
        $insertedUser = $this->userService->insert(
            new User(
                $username,
                $password,
                $isActive
            )
        );

        $insertedUserSectorPermission = $this->userSectorPermissionRepository->insert(
            new UserSectorPermission(
                Id::make($insertedUser->getIdValue()),
                Id::make($insertedSector->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $oneWeekLogin = true;
        $newResult = $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                $password,
                $oneWeekLogin
            )
        );

        $this->assertEquals(AuthenticationLoginStates::New, $newResult->getState());
        $this->assertNotEmpty($newResult->getToken());
        $this->assertEquals(
            new AuthenticationData(
                Id::make($insertedUser->getIdValue()),
                Username::make($insertedUser->getUsernameValue()),
                new UserSectorPermissionCollection([
                    $insertedUserSectorPermission
                ])
            ),
            $newResult->getData()
        );

        $interval = new \DateInterval("P7D");

        $this->expectException(TokenCacheException::class);

        $this->tokenCacheClock->advance(
            $interval
        );

        $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                $password,
                $oneWeekLogin
            )
        );
    }

    public function testIfAOneWeekExistantLoginByAUserWithPermissionsToSectorsDoesNotLoginAfterOneWeekBecauseOfExpiredTokenWhenValidating(): void
    {
        $insertedPermission = $this->permissionRepository->insert(
            new Permission(
                Name::make("permission"),
                PermissionValue::make("a"),
                true
            )
        );

        $insertedSector = $this->sectorRepository->insert(
            new Sector(
                Name::make("sector"),
                SectorValue::make("a"),
                true
            )
        );

        $username = Username::make("test");
        $password = DecodedPassword::make("test");
        $isActive = true;
        $insertedUser = $this->userService->insert(
            new User(
                $username,
                $password,
                $isActive
            )
        );

        $insertedUserSectorPermission = $this->userSectorPermissionRepository->insert(
            new UserSectorPermission(
                Id::make($insertedUser->getIdValue()),
                Id::make($insertedSector->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $oneWeekLogin = true;
        $newResult = $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                $password,
                $oneWeekLogin
            )
        );

        $this->assertEquals(AuthenticationLoginStates::New, $newResult->getState());
        $this->assertNotEmpty($newResult->getToken());
        $this->assertEquals(
            new AuthenticationData(
                Id::make($insertedUser->getIdValue()),
                Username::make($insertedUser->getUsernameValue()),
                new UserSectorPermissionCollection([
                    $insertedUserSectorPermission
                ])
            ),
            $newResult->getData()
        );

        $interval = new \DateInterval("P7D");

        $this->expectException(AuthenticationTokenDecoderException::class);

        $this->authenticationTokenClock->advance(
            $interval
        );

        $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                $password,
                $oneWeekLogin
            )
        );
    }

    public function testIfAEmittedTokenValidForOneDayToAUserWithPermissionsToSectorsIsStillValidInThatDay(): void
    {
        $insertedPermission = $this->permissionRepository->insert(
            new Permission(
                Name::make("permission"),
                PermissionValue::make("a"),
                true
            )
        );

        $insertedSector = $this->sectorRepository->insert(
            new Sector(
                Name::make("sector"),
                SectorValue::make("a"),
                true
            )
        );

        $username = Username::make("test");
        $password = DecodedPassword::make("test");
        $isActive = true;
        $insertedUser = $this->userService->insert(
            new User(
                $username,
                $password,
                $isActive
            )
        );

        $insertedUserSectorPermission = $this->userSectorPermissionRepository->insert(
            new UserSectorPermission(
                Id::make($insertedUser->getIdValue()),
                Id::make($insertedSector->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $oneWeekLogin = false;
        $newResult = $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                $password,
                $oneWeekLogin
            )
        );

        $this->assertEquals(AuthenticationLoginStates::New, $newResult->getState());
        $this->assertNotEmpty($newResult->getToken());
        $this->assertEquals(
            new AuthenticationData(
                Id::make($insertedUser->getIdValue()),
                Username::make($insertedUser->getUsernameValue()),
                new UserSectorPermissionCollection([
                    $insertedUserSectorPermission
                ])
            ),
            $newResult->getData()
        );

        $validationResult = $this->authenticationService->validateToken(
            $newResult->getToken()
        );

        $this->assertEquals(
            $newResult->getToken()->getToken(),
            $validationResult->getToken()->getToken()
        );
        $this->assertEquals(
            new AuthenticationData(
                Id::make($insertedUser->getIdValue()),
                Username::make($insertedUser->getUsernameValue()),
                new UserSectorPermissionCollection([
                    $insertedUserSectorPermission
                ])
            ),
            new AuthenticationData(
                $validationResult->getUserId(),
                $validationResult->getUsername(),
                $validationResult->getUserSectorPermissionCollection()
            )
        );
    }

    public function testIfAEmittedTokenValidForOneDayToAUserWithPermissionsToSectorsTurnsInvalidAfterOneDayBecauseOfExpiredTokenOnCache(): void
    {
        $insertedPermission = $this->permissionRepository->insert(
            new Permission(
                Name::make("permission"),
                PermissionValue::make("a"),
                true
            )
        );

        $insertedSector = $this->sectorRepository->insert(
            new Sector(
                Name::make("sector"),
                SectorValue::make("a"),
                true
            )
        );

        $username = Username::make("test");
        $password = DecodedPassword::make("test");
        $isActive = true;
        $insertedUser = $this->userService->insert(
            new User(
                $username,
                $password,
                $isActive
            )
        );

        $insertedUserSectorPermission = $this->userSectorPermissionRepository->insert(
            new UserSectorPermission(
                Id::make($insertedUser->getIdValue()),
                Id::make($insertedSector->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $oneWeekLogin = false;
        $newResult = $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                $password,
                $oneWeekLogin
            )
        );

        $this->assertEquals(AuthenticationLoginStates::New, $newResult->getState());
        $this->assertNotEmpty($newResult->getToken());
        $this->assertEquals(
            new AuthenticationData(
                Id::make($insertedUser->getIdValue()),
                Username::make($insertedUser->getUsernameValue()),
                new UserSectorPermissionCollection([
                    $insertedUserSectorPermission
                ])
            ),
            $newResult->getData()
        );

        $interval = new \DateInterval("P1D");
        $this->tokenCacheClock->advance(
            $interval
        );

        $this->expectException(TokenCacheException::class);

        $this->authenticationService->validateToken(
            $newResult->getToken()
        );
    }

    public function testIfAEmittedTokenValidForOneDayToAUserWithPermissionsToSectorsTurnsInvalidAfterOneDayBecauseOfExpiredTokenWhenValidating(): void
    {
        $insertedPermission = $this->permissionRepository->insert(
            new Permission(
                Name::make("permission"),
                PermissionValue::make("a"),
                true
            )
        );

        $insertedSector = $this->sectorRepository->insert(
            new Sector(
                Name::make("sector"),
                SectorValue::make("a"),
                true
            )
        );

        $username = Username::make("test");
        $password = DecodedPassword::make("test");
        $isActive = true;
        $insertedUser = $this->userService->insert(
            new User(
                $username,
                $password,
                $isActive
            )
        );

        $insertedUserSectorPermission = $this->userSectorPermissionRepository->insert(
            new UserSectorPermission(
                Id::make($insertedUser->getIdValue()),
                Id::make($insertedSector->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $oneWeekLogin = false;
        $newResult = $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                $password,
                $oneWeekLogin
            )
        );

        $this->assertEquals(AuthenticationLoginStates::New, $newResult->getState());
        $this->assertNotEmpty($newResult->getToken());
        $this->assertEquals(
            new AuthenticationData(
                Id::make($insertedUser->getIdValue()),
                Username::make($insertedUser->getUsernameValue()),
                new UserSectorPermissionCollection([
                    $insertedUserSectorPermission
                ])
            ),
            $newResult->getData()
        );

        $interval = new \DateInterval("P1D");
        $this->authenticationTokenClock->advance(
            $interval
        );

        $this->expectException(AuthenticationTokenDecoderException::class);

        $this->authenticationService->validateToken(
            $newResult->getToken()
        );
    }

    public function testIfAEmittedTokenValidForOneWeekToAUserWithPermissionsToSectorsIsStillValidInThatDay(): void
    {
        $insertedPermission = $this->permissionRepository->insert(
            new Permission(
                Name::make("permission"),
                PermissionValue::make("a"),
                true
            )
        );

        $insertedSector = $this->sectorRepository->insert(
            new Sector(
                Name::make("sector"),
                SectorValue::make("a"),
                true
            )
        );

        $username = Username::make("test");
        $password = DecodedPassword::make("test");
        $isActive = true;
        $insertedUser = $this->userService->insert(
            new User(
                $username,
                $password,
                $isActive
            )
        );

        $insertedUserSectorPermission = $this->userSectorPermissionRepository->insert(
            new UserSectorPermission(
                Id::make($insertedUser->getIdValue()),
                Id::make($insertedSector->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $oneWeekLogin = true;
        $newResult = $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                $password,
                $oneWeekLogin
            )
        );

        $this->assertEquals(AuthenticationLoginStates::New, $newResult->getState());
        $this->assertNotEmpty($newResult->getToken());
        $this->assertEquals(
            new AuthenticationData(
                Id::make($insertedUser->getIdValue()),
                Username::make($insertedUser->getUsernameValue()),
                new UserSectorPermissionCollection([
                    $insertedUserSectorPermission
                ])
            ),
            $newResult->getData()
        );

        $interval = new \DateInterval("P1D");
        $this->tokenCacheClock->advance(
            $interval
        );
        $this->authenticationTokenClock->advance(
            $interval
        );

        $validationResult = $this->authenticationService->validateToken(
            $newResult->getToken()
        );

        $this->assertEquals(
            $newResult->getToken()->getToken(),
            $validationResult->getToken()->getToken()
        );
        $this->assertEquals(
            new AuthenticationData(
                Id::make($insertedUser->getIdValue()),
                Username::make($insertedUser->getUsernameValue()),
                new UserSectorPermissionCollection([
                    $insertedUserSectorPermission
                ])
            ),
            new AuthenticationData(
                $validationResult->getUserId(),
                $validationResult->getUsername(),
                $validationResult->getUserSectorPermissionCollection()
            )
        );
    }

    public function testIfAEmittedTokenValidForOneWeekToAUserWithPermissionsToSectorsIsStillValidAfterOneDay(): void
    {
        $insertedPermission = $this->permissionRepository->insert(
            new Permission(
                Name::make("permission"),
                PermissionValue::make("a"),
                true
            )
        );

        $insertedSector = $this->sectorRepository->insert(
            new Sector(
                Name::make("sector"),
                SectorValue::make("a"),
                true
            )
        );

        $username = Username::make("test");
        $password = DecodedPassword::make("test");
        $isActive = true;
        $insertedUser = $this->userService->insert(
            new User(
                $username,
                $password,
                $isActive
            )
        );

        $insertedUserSectorPermission = $this->userSectorPermissionRepository->insert(
            new UserSectorPermission(
                Id::make($insertedUser->getIdValue()),
                Id::make($insertedSector->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $oneWeekLogin = true;
        $newResult = $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                $password,
                $oneWeekLogin
            )
        );

        $this->assertEquals(AuthenticationLoginStates::New, $newResult->getState());
        $this->assertNotEmpty($newResult->getToken());
        $this->assertEquals(
            new AuthenticationData(
                Id::make($insertedUser->getIdValue()),
                Username::make($insertedUser->getUsernameValue()),
                new UserSectorPermissionCollection([
                    $insertedUserSectorPermission
                ])
            ),
            $newResult->getData()
        );

        $interval = new \DateInterval("P1D");
        $this->tokenCacheClock->advance(
            $interval
        );
        $this->authenticationTokenClock->advance(
            $interval
        );

        $validationResult = $this->authenticationService->validateToken(
            $newResult->getToken()
        );

        $this->assertEquals(
            $newResult->getToken()->getToken(),
            $validationResult->getToken()->getToken()
        );
        $this->assertEquals(
            new AuthenticationData(
                Id::make($insertedUser->getIdValue()),
                Username::make($insertedUser->getUsernameValue()),
                new UserSectorPermissionCollection([
                    $insertedUserSectorPermission
                ])
            ),
            new AuthenticationData(
                $validationResult->getUserId(),
                $validationResult->getUsername(),
                $validationResult->getUserSectorPermissionCollection()
            )
        );
    }

    public function testIfAEmittedTokenValidForOneWeekToAUserWithPermissionsToSectorsTurnsInvalidAfterOneWeekBecauseOfExpiredTokenOnCache(): void
    {
        $insertedPermission = $this->permissionRepository->insert(
            new Permission(
                Name::make("permission"),
                PermissionValue::make("a"),
                true
            )
        );

        $insertedSector = $this->sectorRepository->insert(
            new Sector(
                Name::make("sector"),
                SectorValue::make("a"),
                true
            )
        );

        $username = Username::make("test");
        $password = DecodedPassword::make("test");
        $isActive = true;
        $insertedUser = $this->userService->insert(
            new User(
                $username,
                $password,
                $isActive
            )
        );

        $insertedUserSectorPermission = $this->userSectorPermissionRepository->insert(
            new UserSectorPermission(
                Id::make($insertedUser->getIdValue()),
                Id::make($insertedSector->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $oneWeekLogin = true;
        $newResult = $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                $password,
                $oneWeekLogin
            )
        );

        $this->assertEquals(AuthenticationLoginStates::New, $newResult->getState());
        $this->assertNotEmpty($newResult->getToken());
        $this->assertEquals(
            new AuthenticationData(
                Id::make($insertedUser->getIdValue()),
                Username::make($insertedUser->getUsernameValue()),
                new UserSectorPermissionCollection([
                    $insertedUserSectorPermission
                ])
            ),
            $newResult->getData()
        );

        $interval = new \DateInterval("P7D");

        $this->tokenCacheClock->advance(
            $interval
        );

        $this->expectException(TokenCacheException::class);

        $this->authenticationService->validateToken(
            $newResult->getToken()
        );
    }

    public function testIfAEmittedTokenValidForOneWeekToAUserWithPermissionsToSectorsTurnsInvalidAfterOneWeekBecauseOfExpiredTokenWhenValidating(): void
    {
        $insertedPermission = $this->permissionRepository->insert(
            new Permission(
                Name::make("permission"),
                PermissionValue::make("a"),
                true
            )
        );

        $insertedSector = $this->sectorRepository->insert(
            new Sector(
                Name::make("sector"),
                SectorValue::make("a"),
                true
            )
        );

        $username = Username::make("test");
        $password = DecodedPassword::make("test");
        $isActive = true;
        $insertedUser = $this->userService->insert(
            new User(
                $username,
                $password,
                $isActive
            )
        );

        $insertedUserSectorPermission = $this->userSectorPermissionRepository->insert(
            new UserSectorPermission(
                Id::make($insertedUser->getIdValue()),
                Id::make($insertedSector->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $oneWeekLogin = true;
        $newResult = $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                $password,
                $oneWeekLogin
            )
        );

        $this->assertEquals(AuthenticationLoginStates::New, $newResult->getState());
        $this->assertNotEmpty($newResult->getToken());
        $this->assertEquals(
            new AuthenticationData(
                Id::make($insertedUser->getIdValue()),
                Username::make($insertedUser->getUsernameValue()),
                new UserSectorPermissionCollection([
                    $insertedUserSectorPermission
                ])
            ),
            $newResult->getData()
        );

        $interval = new \DateInterval("P7D");

        $this->authenticationTokenClock->advance(
            $interval
        );

        $this->expectException(AuthenticationTokenDecoderException::class);

        $this->authenticationService->validateToken(
            $newResult->getToken()
        );
    }

    public function testIfAInvalidTokenDoesNotValidate(): void
    {
        $this->expectException(AuthenticationTokenDecoderException::class);

        $this->authenticationService->validateToken(
            new EncodedAuthenticationToken(
                "batata"
            )
        );
    }

    public function testIfALogoffSuccedsWithAValidTokenInformedByAUserWithPermissionsToSectors(): void
    {
        $insertedPermission = $this->permissionRepository->insert(
            new Permission(
                Name::make("permission"),
                PermissionValue::make("a"),
                true
            )
        );

        $insertedSector = $this->sectorRepository->insert(
            new Sector(
                Name::make("sector"),
                SectorValue::make("a"),
                true
            )
        );

        $username = Username::make("test");
        $password = DecodedPassword::make("test");
        $isActive = true;
        $insertedUser = $this->userService->insert(
            new User(
                $username,
                $password,
                $isActive
            )
        );

        $this->userSectorPermissionRepository->insert(
            new UserSectorPermission(
                Id::make($insertedUser->getIdValue()),
                Id::make($insertedSector->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $oneWeekLogin = true;
        $result = $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                $password,
                $oneWeekLogin
            )
        );

        $this->expectNotToPerformAssertions();

        $this->authenticationService->tryLogoff(
            $result->getToken()
        );
    }

    public function testIfAInvalidTokenDoesNotLogoff(): void
    {
        $this->expectException(AuthenticationTokenDecoderException::class);

        $this->authenticationService->tryLogoff(
            new EncodedAuthenticationToken(
                "batata"
            )
        );
    }
    */
}
