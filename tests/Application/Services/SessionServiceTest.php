<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Tests\Application\Services;

use Mvreisg\GamebaseBackend\Application\Services\Authentication\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\Session\Login\Parameters\SessionLoginParameters;
use Mvreisg\GamebaseBackend\Application\Services\Session\Login\Return\SessionLoginReturn;
use Mvreisg\GamebaseBackend\Application\Services\Session\SessionService;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Decoder\AuthenticationTokenDecoder;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Decoder\Exceptions\AuthenticationTokenDecoderException;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Encoder\AuthenticationTokenEncoder;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Validate\AuthenticationTokenValidator;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Data\Decoded\DecodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Data\Encoded\EncodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Cache\Token\Interface\TokenCacheInterface;
use Mvreisg\GamebaseBackend\Domain\Encryption\Interface\EncryptionInterface;
use Mvreisg\GamebaseBackend\Domain\Entities\DecodedPassword;
use Mvreisg\GamebaseBackend\Domain\Entities\Exceptions\EntityException;
use Mvreisg\GamebaseBackend\Domain\Entities\Id;
use Mvreisg\GamebaseBackend\Domain\Entities\User;
use Mvreisg\GamebaseBackend\Domain\Entities\Username;
use Mvreisg\GamebaseBackend\Domain\Entities\UserSectorPermission;
use Mvreisg\GamebaseBackend\Domain\Entities\UserSectorPermissionCollection;
use Mvreisg\GamebaseBackend\Domain\Interfaces\ClockInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\UserRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\UserSectorPermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Session\Data\SessionData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SessionServiceTest extends TestCase
{
    private function createUser(): User
    {
        $id = Id::make(1);
        $username = Username::make("test");
        $password = DecodedPassword::make("test");
        $isActive = true;
        $user = new User(
            $username,
            $password,
            $isActive
        );
        $user->setId($id);
        return $user;
    }

    private function createUserRepository(
        User $user
    ): MockObject&\Mvreisg\GamebaseBackend\Domain\Repositories\Interface\UserRepositoryInterface {
        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository
            ->method("findByUsername")
            ->willReturn($user);
        return $userRepository;
    }

    private function createEncrypter(): MockObject&\Mvreisg\GamebaseBackend\Domain\Encryption\Interface\EncryptionInterface
    {
        $encrypter = $this->createMock(EncryptionInterface::class);
        $encrypter
            ->method("decrypt")
            ->willReturn("test");
        return $encrypter;
    }

    private function createUserSectorPermissionCollection(): UserSectorPermissionCollection
    {
        $userSectorPermissions = new UserSectorPermissionCollection();
        for ($i = 0; $i < 3; $i++) {
            $userSectorPermission = new UserSectorPermission(
                Id::make($i + 1),
                Id::make($i + 1),
                Id::make($i + 1),
            );
            $userSectorPermissions->add($userSectorPermission);
        }
        return $userSectorPermissions;
    }

    private function createUserSectorPermissionRepository(
        UserSectorPermissionCollection $userSectorPermissionCollection
    ): MockObject&\Mvreisg\GamebaseBackend\Domain\Repositories\Interface\UserSectorPermissionRepositoryInterface {
        $userSectorPermissionRepository = $this->createMock(UserSectorPermissionRepositoryInterface::class);
        $userSectorPermissionRepository
            ->method("findAllByUserId")
            ->willReturn($userSectorPermissionCollection);
        return $userSectorPermissionRepository;
    }

    private function createSessionData(
        User $user,
        UserSectorPermissionCollection $userSectorPermissionCollection
    ): MockObject&\Mvreisg\GamebaseBackend\Domain\Session\Data\SessionData {
        $sessionData = $this->createMock(SessionData::class);
        $sessionData
            ->method("getUserId")
            ->willReturn($user->getId());

        $sessionData
            ->method("getUsername")
            ->willReturn($user->getUsername());

        $sessionData
            ->method("getUserSectorPermissionCollection")
            ->willReturn($userSectorPermissionCollection);

        return $sessionData;
    }

    private function createSessionLoginParameters(
        User $user,
        bool $oneWeekLogin
    ): MockObject&\Mvreisg\GamebaseBackend\Application\Services\Session\Login\Parameters\SessionLoginParameters {
        $sessionLoginParameters = $this->createMock(SessionLoginParameters::class);
        $sessionLoginParameters
            ->method("getUsername")
            ->willReturn($user->getUsername());
        $sessionLoginParameters
            ->method("getPassword")
            ->willReturn($user->getPassword());
        $sessionLoginParameters
            ->method("getOneWeekLogin")
            ->willReturn($oneWeekLogin);
        return $sessionLoginParameters;
    }

    private function createEncodedToken(
        string $token
    ): MockObject&\Mvreisg\GamebaseBackend\Domain\Authentication\Token\Data\Encoded\EncodedAuthenticationToken {
        $encodedToken = $this->createMock(EncodedAuthenticationToken::class);
        $encodedToken
            ->method("getToken")
            ->willReturn($token);
        return $encodedToken;
    }

    private function createSessionService(
        MockObject&\Mvreisg\GamebaseBackend\Domain\Repositories\Interface\UserRepositoryInterface $userRepository,
        MockObject&\Mvreisg\GamebaseBackend\Domain\Cache\Token\Interface\TokenCacheInterface $tokenCache,
        MockObject&\Mvreisg\GamebaseBackend\Domain\Encryption\Interface\EncryptionInterface $encrypter,
        MockObject&\Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Encoder\AuthenticationTokenEncoder $authenticationTokenEncoder,
        MockObject&\Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Decoder\AuthenticationTokenDecoder $authenticationTokenDecoder,
        AuthenticationTokenValidator $authenticationTokenValidator,
        MockObject&\Mvreisg\GamebaseBackend\Domain\Repositories\Interface\UserSectorPermissionRepositoryInterface $userSectorPermissionRepository
    ): SessionService {
        $authenticationService = new AuthenticationService(
            $tokenCache,
            $authenticationTokenDecoder,
            $authenticationTokenValidator
        );
        return new SessionService(
            $authenticationService,
            $userRepository,
            $tokenCache,
            $encrypter,
            $authenticationTokenEncoder,
            $userSectorPermissionRepository,
        );
    }

    private function createTokenCache(
        User $user,
        EncodedAuthenticationToken $token
    ): MockObject&\Mvreisg\GamebaseBackend\Domain\Cache\Token\Interface\TokenCacheInterface {
        $tokenCache = $this->createMock(TokenCacheInterface::class);
        $tokenCache
            ->method("exists")
            ->willReturnOnConsecutiveCalls(
                false,
                true
            );
        $tokenCache
            ->method("set")
            ->with($user->getUsername(), $token);
        $tokenCache
            ->method("get")
            ->willReturn(
                $token
            );
        return $tokenCache;
    }

    private function createDecodedToken(
        User $user,
        \DateTimeImmutable $issuedAt,
        \DateTimeImmutable $expiredAt,
    ): MockObject&\Mvreisg\GamebaseBackend\Domain\Authentication\Token\Data\Decoded\DecodedAuthenticationToken {
        $decodedToken = $this->createMock(DecodedAuthenticationToken::class);

        $decodedToken
            ->method("getUserId")
            ->willReturn($user->getId());

        $decodedToken
            ->method("getUsername")
            ->willReturn($user->getUsername());

        $decodedToken
            ->method("getIssuedAt")
            ->willReturn($issuedAt);

        $decodedToken
            ->method("getExpiresAt")
            ->willReturn($expiredAt);

        return $decodedToken;
    }

    private function createAuthenticationTokenEncoder(
        MockObject&\Mvreisg\GamebaseBackend\Domain\Authentication\Token\Data\Encoded\EncodedAuthenticationToken $token
    ): MockObject&\Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Encoder\AuthenticationTokenEncoder {
        $authenticationTokenEncoder = $this->createMock(AuthenticationTokenEncoder::class);
        $authenticationTokenEncoder
            ->method("encode")
            ->willReturn($token);
        return $authenticationTokenEncoder;
    }

    private function createAuthenticationTokenDecoder(
        MockObject&\Mvreisg\GamebaseBackend\Domain\Authentication\Token\Data\Decoded\DecodedAuthenticationToken $token
    ): MockObject&\Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Decoder\AuthenticationTokenDecoder {
        $authenticationTokenDecoder = $this->createMock(AuthenticationTokenDecoder::class);
        $authenticationTokenDecoder
            ->method("decode")
            ->willReturn($token);
        return $authenticationTokenDecoder;
    }

    private function assertLoginResultEquals(
        User $user,
        UserSectorPermissionCollection $userSectorPermissionCollection,
        EncodedAuthenticationToken $token,
        SessionLoginReturn $result
    ): void {
        $this->assertEquals(
            $user->getId(),
            $result->getData()->getUserId()
        );
        $this->assertEquals(
            $user->getUsername(),
            $result->getData()->getUsername()
        );
        $this->assertEquals(
            $userSectorPermissionCollection,
            $result->getData()->getUserSectorPermissionCollection()
        );
        $this->assertEquals(
            $token,
            $result->getToken()
        );
    }

    public function testIfANewLoginValidForOneDayWithARegisteredUserWithoutPermissionsSucceds(): void
    {
        $user = $this->createUser();

        $userRepository = $this->createUserRepository($user);

        $tokenCache = $this->createMock(TokenCacheInterface::class);
        $authenticationTokenDecoder = $this->createMock(AuthenticationTokenDecoder::class);
        $encrypter = $this->createEncrypter();

        $userSectorPermissionCollection = $this->createUserSectorPermissionCollection();

        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository(
            $userSectorPermissionCollection
        );

        $token = $this->createMock(EncodedAuthenticationToken::class);

        $authenticationTokenEncoder = $this->createAuthenticationTokenEncoder($token);

        $now = new \DateTimeImmutable();
        $clock = $this->createMock(ClockInterface::class);
        $clock
            ->method("now")
            ->willReturn($now);

        $authenticationTokenValidator = new AuthenticationTokenValidator(
            $clock
        );

        $sessionService = $this->createSessionService(
            $userRepository,
            $tokenCache,
            $encrypter,
            $authenticationTokenEncoder,
            $authenticationTokenDecoder,
            $authenticationTokenValidator,
            $userSectorPermissionRepository,
            $clock
        );

        $sessionLoginParameters = $this->createSessionLoginParameters($user, false);

        $result = $sessionService->login(
            $sessionLoginParameters
        );

        $this->assertLoginResultEquals(
            $user,
            $userSectorPermissionCollection,
            $token,
            $result
        );
    }

    public function testIfANewLoginValidForOneDayWithARegisteredUserWithPermissionsToSectorsSucceds(): void
    {
        $user = $this->createUser();

        $userRepository = $this->createUserRepository($user);

        $tokenCache = $this->createMock(TokenCacheInterface::class);
        $authenticationTokenDecoder = $this->createMock(AuthenticationTokenDecoder::class);
        $encrypter = $this->createMock(EncryptionInterface::class);
        $encrypter = $this->createEncrypter();

        $userSectorPermissionCollection = $this->createUserSectorPermissionCollection();

        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository(
            $userSectorPermissionCollection
        );

        $token = $this->createMock(EncodedAuthenticationToken::class);

        $authenticationTokenEncoder = $this->createAuthenticationTokenEncoder($token);

        $now = new \DateTimeImmutable();
        $clock = $this->createMock(ClockInterface::class);
        $clock
            ->method("now")
            ->willReturn($now);

        $authenticationTokenValidator = new AuthenticationTokenValidator(
            $clock
        );

        $sessionService = $this->createSessionService(
            $userRepository,
            $tokenCache,
            $encrypter,
            $authenticationTokenEncoder,
            $authenticationTokenDecoder,
            $authenticationTokenValidator,
            $userSectorPermissionRepository,
            $clock
        );

        $sessionLoginParameters = $this->createSessionLoginParameters($user, false);

        $result = $sessionService->login(
            $sessionLoginParameters
        );

        $this->assertLoginResultEquals(
            $user,
            $userSectorPermissionCollection,
            $token,
            $result
        );
    }

    public function testIfANewLoginValidForOneWeekWithARegisteredUserWithoutPermissionsSucceds(): void
    {
        $user = $this->createUser();

        $userRepository = $this->createUserRepository($user);

        $tokenCache = $this->createMock(TokenCacheInterface::class);
        $authenticationTokenDecoder = $this->createMock(AuthenticationTokenDecoder::class);
        $encrypter = $this->createEncrypter();

        $userSectorPermissionCollection = $this->createUserSectorPermissionCollection();

        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository(
            $userSectorPermissionCollection
        );

        $token = $this->createMock(EncodedAuthenticationToken::class);

        $authenticationTokenEncoder = $this->createAuthenticationTokenEncoder($token);

        $now = new \DateTimeImmutable();
        $clock = $this->createMock(ClockInterface::class);
        $clock
            ->method("now")
            ->willReturn($now);

        $authenticationTokenValidator = new AuthenticationTokenValidator(
            $clock
        );

        $sessionService = $this->createSessionService(
            $userRepository,
            $tokenCache,
            $encrypter,
            $authenticationTokenEncoder,
            $authenticationTokenDecoder,
            $authenticationTokenValidator,
            $userSectorPermissionRepository,
            $clock
        );

        $sessionLoginParameters = $this->createSessionLoginParameters($user, false);

        $result = $sessionService->login(
            $sessionLoginParameters
        );

        $this->assertLoginResultEquals(
            $user,
            $userSectorPermissionCollection,
            $token,
            $result
        );
    }

    public function testIfANewLoginValidForOneWeekWithARegisteredUserWithPermissionToSectorsSucceds(): void
    {
        $user = $this->createUser();

        $userRepository = $this->createUserRepository($user);

        $tokenCache = $this->createMock(TokenCacheInterface::class);
        $authenticationTokenDecoder = $this->createMock(AuthenticationTokenDecoder::class);
        $encrypter = $this->createEncrypter();

        $userSectorPermissionCollection = $this->createUserSectorPermissionCollection();

        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository(
            $userSectorPermissionCollection
        );

        $token = $this->createMock(EncodedAuthenticationToken::class);

        $authenticationTokenEncoder = $this->createAuthenticationTokenEncoder($token);

        $now = new \DateTimeImmutable();
        $clock = $this->createMock(ClockInterface::class);
        $clock
            ->method("now")
            ->willReturn($now);

        $authenticationTokenValidator = new AuthenticationTokenValidator(
            $clock
        );

        $sessionService = $this->createSessionService(
            $userRepository,
            $tokenCache,
            $encrypter,
            $authenticationTokenEncoder,
            $authenticationTokenDecoder,
            $authenticationTokenValidator,
            $userSectorPermissionRepository,
            $clock
        );

        $sessionLoginParameters = $this->createSessionLoginParameters($user, false);

        $result = $sessionService->login(
            $sessionLoginParameters
        );

        $this->assertLoginResultEquals(
            $user,
            $userSectorPermissionCollection,
            $token,
            $result
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
        $user = $this->createUser();

        $userRepository = $this->createUserRepository($user);

        $tokenCache = $this->createMock(TokenCacheInterface::class);
        $authenticationTokenDecoder = $this->createMock(AuthenticationTokenDecoder::class);
        $encrypter = $this->createEncrypter();

        $userSectorPermissionCollection = $this->createUserSectorPermissionCollection();

        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository(
            $userSectorPermissionCollection
        );

        $token = $this->createMock(EncodedAuthenticationToken::class);

        $authenticationTokenEncoder = $this->createAuthenticationTokenEncoder($token);

        $now = new \DateTimeImmutable();
        $clock = $this->createMock(ClockInterface::class);
        $clock
            ->method("now")
            ->willReturn($now);

        $authenticationTokenValidator = new AuthenticationTokenValidator(
            $clock
        );

        $sessionService = $this->createSessionService(
            $userRepository,
            $tokenCache,
            $encrypter,
            $authenticationTokenEncoder,
            $authenticationTokenDecoder,
            $authenticationTokenValidator,
            $userSectorPermissionRepository,
            $clock
        );

        $sessionLoginParameters = $this->createSessionLoginParameters($user, false);

        $result = $sessionService->login(
            $sessionLoginParameters
        );

        $this->assertLoginResultEquals(
            $user,
            $userSectorPermissionCollection,
            $token,
            $result
        );
    }

    public function testIfANewLoginValidForOneWeekWithInvalidCredentialsFails(): void
    {
        $user = $this->createUser();

        $userRepository = $this->createUserRepository($user);

        $tokenCache = $this->createMock(TokenCacheInterface::class);
        $authenticationTokenDecoder = $this->createMock(AuthenticationTokenDecoder::class);
        $encrypter = $this->createEncrypter();

        $userSectorPermissionCollection = $this->createUserSectorPermissionCollection();

        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository(
            $userSectorPermissionCollection
        );

        $token = $this->createMock(EncodedAuthenticationToken::class);

        $authenticationTokenEncoder = $this->createAuthenticationTokenEncoder($token);

        $now = new \DateTimeImmutable();
        $clock = $this->createMock(ClockInterface::class);
        $clock
            ->method("now")
            ->willReturn($now);

        $authenticationTokenValidator = new AuthenticationTokenValidator(
            $clock
        );

        $sessionService = $this->createSessionService(
            $userRepository,
            $tokenCache,
            $encrypter,
            $authenticationTokenEncoder,
            $authenticationTokenDecoder,
            $authenticationTokenValidator,
            $userSectorPermissionRepository,
            $clock
        );

        $sessionLoginParameters = $this->createSessionLoginParameters($user, false);

        $result = $sessionService->login(
            $sessionLoginParameters
        );

        $this->assertLoginResultEquals(
            $user,
            $userSectorPermissionCollection,
            $token,
            $result
        );
    }

    public function testIfAExistantLoginValidForOneDayWithARegisteredUserWithPermissionsToSectorsSucceds(): void
    {
        $user = $this->createUser();

        $userRepository = $this->createUserRepository($user);

        $encodedToken = $this->createMock(EncodedAuthenticationToken::class);
        $tokenCache = $this->createTokenCache($user, $encodedToken);

        $now = new \DateTimeImmutable();
        $decodedToken = $this->createDecodedToken($user, $now, $now->modify("+1 day"));

        $authenticationTokenDecoder = $this->createAuthenticationTokenDecoder($decodedToken);

        $encrypter = $this->createEncrypter();

        $userSectorPermissionCollection = $this->createUserSectorPermissionCollection();

        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository(
            $userSectorPermissionCollection
        );

        $authenticationTokenEncoder = $this->createAuthenticationTokenEncoder($encodedToken);

        $now = new \DateTimeImmutable();
        $clock = $this->createMock(ClockInterface::class);
        $clock
            ->method("now")
            ->willReturn($now);

        $authenticationTokenValidator = new AuthenticationTokenValidator(
            $clock
        );

        $sessionService = $this->createSessionService(
            $userRepository,
            $tokenCache,
            $encrypter,
            $authenticationTokenEncoder,
            $authenticationTokenDecoder,
            $authenticationTokenValidator,
            $userSectorPermissionRepository,
            $clock
        );

        $sessionLoginParameters = $this->createSessionLoginParameters($user, false);

        $result = $sessionService->login(
            $sessionLoginParameters
        );

        $this->assertLoginResultEquals(
            $user,
            $userSectorPermissionCollection,
            $encodedToken,
            $result
        );

        $result = $sessionService->login(
            $sessionLoginParameters
        );

        $this->assertLoginResultEquals(
            $user,
            $userSectorPermissionCollection,
            $encodedToken,
            $result
        );
    }

    public function testIfAExistantLoginValidForOneWeekWithARegisteredUserWithPermissionToSectorsSucceds(): void
    {
        $user = $this->createUser();

        $userRepository = $this->createUserRepository($user);

        $encodedToken = $this->createMock(EncodedAuthenticationToken::class);
        $tokenCache = $this->createTokenCache($user, $encodedToken);

        $now = new \DateTimeImmutable();
        $decodedToken = $this->createDecodedToken($user, $now, $now->modify("+1 day"));

        $authenticationTokenDecoder = $this->createAuthenticationTokenDecoder($decodedToken);

        $encrypter = $this->createEncrypter();

        $userSectorPermissionCollection = $this->createUserSectorPermissionCollection();

        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository(
            $userSectorPermissionCollection
        );

        $authenticationTokenEncoder = $this->createAuthenticationTokenEncoder($encodedToken);

        $now = new \DateTimeImmutable();
        $clock = $this->createMock(ClockInterface::class);
        $clock
            ->method("now")
            ->willReturn($now);

        $authenticationTokenValidator = new AuthenticationTokenValidator(
            $clock
        );

        $sessionService = $this->createSessionService(
            $userRepository,
            $tokenCache,
            $encrypter,
            $authenticationTokenEncoder,
            $authenticationTokenDecoder,
            $authenticationTokenValidator,
            $userSectorPermissionRepository,
            $clock
        );

        $sessionLoginParameters = $this->createSessionLoginParameters($user, false);

        $result = $sessionService->login(
            $sessionLoginParameters
        );

        $this->assertLoginResultEquals(
            $user,
            $userSectorPermissionCollection,
            $encodedToken,
            $result
        );

        $result = $sessionService->login(
            $sessionLoginParameters
        );

        $this->assertLoginResultEquals(
            $user,
            $userSectorPermissionCollection,
            $encodedToken,
            $result
        );
    }

    public function testIfAOneDayExistantLoginByAUserWithPermissionsToSectorsDoesNotLoginAfterOneDayBecauseOfExpiredToken(): void
    {
        $user = $this->createUser();

        $userRepository = $this->createUserRepository($user);

        $encodedToken = $this->createMock(EncodedAuthenticationToken::class);
        $tokenCache = $this->createTokenCache($user, $encodedToken);

        $now = new \DateTimeImmutable();
        $expiresAt = $now->modify("+1 day");
        $decodedToken = $this->createDecodedToken($user, $now, $expiresAt);

        $authenticationTokenDecoder = $this->createAuthenticationTokenDecoder($decodedToken);

        $encrypter = $this->createEncrypter();

        $userSectorPermissionCollection = $this->createUserSectorPermissionCollection();

        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository(
            $userSectorPermissionCollection
        );

        $authenticationTokenEncoder = $this->createAuthenticationTokenEncoder($encodedToken);

        $clock = $this->createMock(ClockInterface::class);
        $clock
            ->method("now")
            ->willReturnOnConsecutiveCalls(
                $now,
                $expiresAt->modify("+1 second")
            );

        $authenticationTokenValidator = new AuthenticationTokenValidator(
            $clock
        );

        $sessionService = $this->createSessionService(
            $userRepository,
            $tokenCache,
            $encrypter,
            $authenticationTokenEncoder,
            $authenticationTokenDecoder,
            $authenticationTokenValidator,
            $userSectorPermissionRepository,
            $clock
        );

        $sessionLoginParameters = $this->createSessionLoginParameters($user, false);

        $result = $sessionService->login(
            $sessionLoginParameters
        );

        $this->assertLoginResultEquals(
            $user,
            $userSectorPermissionCollection,
            $encodedToken,
            $result
        );

        $result = $sessionService->login(
            $sessionLoginParameters
        );

        $this->assertLoginResultEquals(
            $user,
            $userSectorPermissionCollection,
            $encodedToken,
            $result
        );
    }

    public function testIfAOneWeekExistantLoginByAUserWithPermissionsToSectorsDoesNotLoginAfterOneWeekBecauseOfExpiredToken(): void
    {
        $user = $this->createUser();

        $userRepository = $this->createUserRepository($user);

        $encodedToken = $this->createMock(EncodedAuthenticationToken::class);
        $tokenCache = $this->createTokenCache($user, $encodedToken);

        $now = new \DateTimeImmutable();
        $expiresAt = $now->modify("+1 day");
        $decodedToken = $this->createDecodedToken($user, $now, $expiresAt);

        $authenticationTokenDecoder = $this->createAuthenticationTokenDecoder($decodedToken);

        $encrypter = $this->createEncrypter();

        $userSectorPermissionCollection = $this->createUserSectorPermissionCollection();

        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository(
            $userSectorPermissionCollection
        );

        $authenticationTokenEncoder = $this->createAuthenticationTokenEncoder($encodedToken);

        $clock = $this->createMock(ClockInterface::class);
        $clock
            ->method("now")
            ->willReturnOnConsecutiveCalls(
                $now,
                $expiresAt->modify("+1 second")
            );

        $authenticationTokenValidator = new AuthenticationTokenValidator(
            $clock
        );

        $sessionService = $this->createSessionService(
            $userRepository,
            $tokenCache,
            $encrypter,
            $authenticationTokenEncoder,
            $authenticationTokenDecoder,
            $authenticationTokenValidator,
            $userSectorPermissionRepository,
            $clock
        );

        $sessionLoginParameters = $this->createSessionLoginParameters($user, false);

        $result = $sessionService->login(
            $sessionLoginParameters
        );

        $this->assertLoginResultEquals(
            $user,
            $userSectorPermissionCollection,
            $encodedToken,
            $result
        );

        $result = $sessionService->login(
            $sessionLoginParameters
        );

        $this->assertLoginResultEquals(
            $user,
            $userSectorPermissionCollection,
            $encodedToken,
            $result
        );
    }

    public function testIfAOneWeekExistantLoginByAUserWithPermissionsToSectorsDoesStillValidAfterOneDay(): void
    {
        $user = $this->createUser();

        $userRepository = $this->createUserRepository($user);

        $encodedToken = $this->createEncodedToken("teste");

        $tokenCache = $this->createTokenCache($user, $encodedToken);

        $now = new \DateTimeImmutable();
        $expiresAt = $now->modify("+1 week");
        $decodedToken = $this->createDecodedToken($user, $now, $expiresAt);

        $authenticationTokenDecoder = $this->createAuthenticationTokenDecoder($decodedToken);

        $encrypter = $this->createEncrypter();

        $userSectorPermissionCollection = $this->createUserSectorPermissionCollection();

        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository(
            $userSectorPermissionCollection
        );

        $authenticationTokenEncoder = $this->createAuthenticationTokenEncoder($encodedToken);

        $clock = $this->createMock(ClockInterface::class);
        $clock
            ->method("now")
            ->willReturnOnConsecutiveCalls(
                $now,
                $now->modify("+1 day")
            );

        $authenticationTokenValidator = new AuthenticationTokenValidator(
            $clock
        );

        $sessionService = $this->createSessionService(
            $userRepository,
            $tokenCache,
            $encrypter,
            $authenticationTokenEncoder,
            $authenticationTokenDecoder,
            $authenticationTokenValidator,
            $userSectorPermissionRepository,
            $clock
        );

        $sessionLoginParameters = $this->createSessionLoginParameters($user, false);

        $result = $sessionService->login(
            $sessionLoginParameters
        );
        var_dump($result->getToken()->getToken());

        $this->assertLoginResultEquals(
            $user,
            $userSectorPermissionCollection,
            $encodedToken,
            $result
        );

        $result = $sessionService->login(
            $sessionLoginParameters
        );
        var_dump($result->getToken()->getToken());

        $this->assertLoginResultEquals(
            $user,
            $userSectorPermissionCollection,
            $encodedToken,
            $result
        );
    }

    public function testIfAInvalidTokenIsInformedToRetrieveData(): void
    {
        $user = $this->createUser();

        $userRepository = $this->createUserRepository($user);

        $encodedToken = $this->createMock(EncodedAuthenticationToken::class);
        $tokenCache = $this->createTokenCache($user, $encodedToken);

        $now = new \DateTimeImmutable();

        $invalidToken = new EncodedAuthenticationToken(
            "batata"
        );

        $authenticationTokenDecoder = $this->createMock(AuthenticationTokenDecoder::class);
        $authenticationTokenDecoder
            ->method("decode")
            ->willThrowException(
                new AuthenticationTokenDecoderException(
                    "Invalid token {$invalidToken->getToken()}."
                )
            );

        $encrypter = $this->createEncrypter();

        $userSectorPermissionCollection = $this->createUserSectorPermissionCollection();

        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository(
            $userSectorPermissionCollection
        );

        $authenticationTokenEncoder = $this->createAuthenticationTokenEncoder($encodedToken);

        $clock = $this->createMock(ClockInterface::class);
        $clock
            ->method("now")
            ->willReturnOnConsecutiveCalls(
                $now,
                $now->modify("+1 day")
            );

        $authenticationTokenValidator = new AuthenticationTokenValidator(
            $clock
        );

        $sessionService = $this->createSessionService(
            $userRepository,
            $tokenCache,
            $encrypter,
            $authenticationTokenEncoder,
            $authenticationTokenDecoder,
            $authenticationTokenValidator,
            $userSectorPermissionRepository,
            $clock
        );

        $this->expectException(AuthenticationTokenDecoderException::class);

        $sessionService->retrieveData(
            $invalidToken
        );
    }
}
