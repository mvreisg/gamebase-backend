<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Tests\Application\Services;

use Mvreisg\GamebaseBackend\Application\Services\Authentication\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\Session\Login\Parameters\SessionLoginParameters;
use Mvreisg\GamebaseBackend\Application\Services\Session\Login\Return\SessionLoginReturn;
use Mvreisg\GamebaseBackend\Application\Services\Session\SessionService;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Decoder\AuthenticationTokenDecoder;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Encoder\AuthenticationTokenEncoder;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Validate\AuthenticationTokenValidator;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Data\Decoded\DecodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Data\Encoded\EncodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Cache\Token\Interface\TokenCacheInterface;
use Mvreisg\GamebaseBackend\Domain\Encryption\Exceptions\EncryptionException;
use Mvreisg\GamebaseBackend\Domain\Encryption\Interface\EncryptionInterface;
use Mvreisg\GamebaseBackend\Domain\Entities\Clock;
use Mvreisg\GamebaseBackend\Domain\Entities\DecodedPassword;
use Mvreisg\GamebaseBackend\Domain\Entities\Id;
use Mvreisg\GamebaseBackend\Domain\Entities\User;
use Mvreisg\GamebaseBackend\Domain\Entities\Username;
use Mvreisg\GamebaseBackend\Domain\Entities\UserSectorPermission;
use Mvreisg\GamebaseBackend\Domain\Entities\UserSectorPermissionCollection;
use Mvreisg\GamebaseBackend\Domain\Interfaces\ClockInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\Exceptions\RepositoryUnexistantRegisterException;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\UserRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\UserSectorPermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Session\Data\SessionData;
use Mvreisg\GamebaseBackend\Domain\Session\Exceptions\InvalidCredentialsException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SessionServiceTest extends TestCase
{
    private function createUser(
        Id $id,
        Username $username,
        DecodedPassword $password,
        bool $isActive
    ): User {
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

    private function createEmptyUserRepository(
        User $user
    ): MockObject&\Mvreisg\GamebaseBackend\Domain\Repositories\Interface\UserRepositoryInterface {
        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository
            ->method("findByUsername")
            ->willThrowException(
                new RepositoryUnexistantRegisterException(
                    "username: {$user->getUsername()->getValue()}"
                )
            );
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

    private function createEncrypterWithDecryptionException(): MockObject&\Mvreisg\GamebaseBackend\Domain\Encryption\Interface\EncryptionInterface
    {
        $encrypter = $this->createMock(EncryptionInterface::class);
        $encrypter
            ->method("decrypt")
            ->willThrowException(
                new EncryptionException(
                    "Decryption failed"
                )
            );
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

    private function createUserSectorPermissionRepositoryWithThrowsExceptionOnFindAllByUserId(
        UserSectorPermissionCollection $userSectorPermissionCollection
    ): MockObject&\Mvreisg\GamebaseBackend\Domain\Repositories\Interface\UserSectorPermissionRepositoryInterface {
        $userSectorPermissionRepository = $this->createMock(UserSectorPermissionRepositoryInterface::class);
        $userSectorPermissionRepository
            ->method("findAllByUserId")
            ->willThrowException(
                new RepositoryUnexistantRegisterException(
                    "user_id: {$userSectorPermissionCollection->fetchAll()[0]->getUserId()->getValue()}"
                )
            );
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
            $authenticationTokenEncoder,
            $authenticationTokenValidator
        );
        return new SessionService(
            $authenticationService,
            $userRepository,
            $tokenCache,
            $encrypter,
            $userSectorPermissionRepository
        );
    }

    private function createTokenCache(
        EncodedAuthenticationToken $token
    ): MockObject&\Mvreisg\GamebaseBackend\Domain\Cache\Token\Interface\TokenCacheInterface {
        $tokenCache = $this->createMock(TokenCacheInterface::class);
        $tokenCache
            ->method("exists")
            ->willReturn(
                true
            );
        $tokenCache
            ->method("get")
            ->willReturn(
                $token
            );
        $tokenCache
            ->method("delete")
            ->willReturn(
                true
            );
        return $tokenCache;
    }

    private function createTokenCacheThatDoesNotDelete(
        EncodedAuthenticationToken $token
    ): MockObject&\Mvreisg\GamebaseBackend\Domain\Cache\Token\Interface\TokenCacheInterface {
        $tokenCache = $this->createMock(TokenCacheInterface::class);
        $tokenCache
            ->method("exists")
            ->willReturn(
                true
            );
        $tokenCache
            ->method("get")
            ->willReturn(
                $token
            );
        $tokenCache
            ->method("delete")
            ->willReturn(
                false
            );
        return $tokenCache;
    }

    private function createEmptyTokenCache(

    ): MockObject&\Mvreisg\GamebaseBackend\Domain\Cache\Token\Interface\TokenCacheInterface {
        $tokenCache = $this->createMock(TokenCacheInterface::class);
        $tokenCache
            ->method("exists")
            ->willReturn(false);
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

    private function createClock(string $timezone): ClockInterface
    {
        $clock = new Clock(
            new \DateTimeZone(
                $timezone
            )
        );
        return $clock;
    }

    /*
    ---------------
    | Login Tests |
    ---------------
    */

    public function testIfLoginSucceds(): void
    {
        $user = $this->createUser(
            Id::make(1),
            Username::make("test"),
            DecodedPassword::make("test"),
            true
        );

        $userRepository = $this->createUserRepository(
            $user
        );
        $encodedToken = $this->createEncodedToken("potato");
        $clock = $this->createClock("UTC");
        $now = $clock->now();
        $issuedAt = $now;
        $expiredAt = $now->modify("+1 hour");
        $decodedToken = $this->createDecodedToken(
            $user,
            $issuedAt,
            $expiredAt
        );

        $tokenCache = $this->createTokenCache(
            $encodedToken
        );
        $authenticationTokenDecoder = $this->createAuthenticationTokenDecoder(
            $decodedToken
        );
        $encrypter = $this->createEncrypter();

        $userSectorPermissionCollection = $this->createUserSectorPermissionCollection();

        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository(
            $userSectorPermissionCollection
        );

        $authenticationTokenEncoder = $this->createAuthenticationTokenEncoder(
            $encodedToken
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
    }

    public function testIfExistantLoginSucceds(): void
    {
        $user = $this->createUser(
            Id::make(1),
            Username::make("test"),
            DecodedPassword::make("test"),
            true
        );

        $userRepository = $this->createUserRepository(
            $user
        );
        $encodedToken = $this->createEncodedToken("potato");
        $clock = $this->createClock("UTC");
        $now = $clock->now();
        $issuedAt = $now;
        $expiredAt = $now->modify("+1 hour");
        $decodedToken = $this->createDecodedToken(
            $user,
            $issuedAt,
            $expiredAt
        );

        $tokenCache = $this->createTokenCache(
            $encodedToken
        );
        $authenticationTokenDecoder = $this->createAuthenticationTokenDecoder(
            $decodedToken
        );
        $encrypter = $this->createEncrypter();

        $userSectorPermissionCollection = $this->createUserSectorPermissionCollection();

        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository(
            $userSectorPermissionCollection
        );

        $authenticationTokenEncoder = $this->createAuthenticationTokenEncoder(
            $encodedToken
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

        $firstResult = $sessionService->login(
            $sessionLoginParameters
        );

        $this->assertLoginResultEquals(
            $user,
            $userSectorPermissionCollection,
            $encodedToken,
            $firstResult
        );

        $secondResult = $sessionService->login(
            $sessionLoginParameters
        );

        $this->assertLoginResultEquals(
            $user,
            $userSectorPermissionCollection,
            $encodedToken,
            $secondResult
        );
    }

    public function testIfLoginFailsByUnexistantUserIdentifiedByUsernameOnRepository(): void
    {
        $this->expectException(RepositoryUnexistantRegisterException::class);

        $unexistantUser = $this->createUser(
            Id::make(1),
            Username::make("test"),
            DecodedPassword::make("test"),
            true
        );

        $userRepository = $this->createEmptyUserRepository(
            $unexistantUser
        );
        $encodedToken = $this->createEncodedToken("potato");
        $clock = $this->createClock("UTC");
        $now = $clock->now();
        $issuedAt = $now;
        $expiredAt = $now->modify("+1 hour");
        $decodedToken = $this->createDecodedToken(
            $unexistantUser,
            $issuedAt,
            $expiredAt
        );

        $tokenCache = $this->createTokenCache(
            $encodedToken
        );
        $authenticationTokenDecoder = $this->createAuthenticationTokenDecoder(
            $decodedToken
        );
        $encrypter = $this->createEncrypter();

        $userSectorPermissionCollection = $this->createUserSectorPermissionCollection();

        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository(
            $userSectorPermissionCollection
        );

        $authenticationTokenEncoder = $this->createAuthenticationTokenEncoder(
            $encodedToken
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

        $sessionLoginParameters = $this->createSessionLoginParameters($unexistantUser, false);

        $sessionService->login(
            $sessionLoginParameters
        );
    }

    public function testIfLoginFailsByDecryptionException(): void
    {
        $this->expectException(EncryptionException::class);

        $user = $this->createUser(
            Id::make(1),
            Username::make("test"),
            DecodedPassword::make("test"),
            true
        );

        $userRepository = $this->createUserRepository(
            $user
        );
        $encodedToken = $this->createEncodedToken("potato");
        $clock = $this->createClock("UTC");
        $now = $clock->now();
        $issuedAt = $now;
        $expiredAt = $now->modify("+1 hour");
        $decodedToken = $this->createDecodedToken(
            $user,
            $issuedAt,
            $expiredAt
        );

        $tokenCache = $this->createTokenCache(
            $encodedToken
        );
        $authenticationTokenDecoder = $this->createAuthenticationTokenDecoder(
            $decodedToken
        );
        $encrypter = $this->createEncrypterWithDecryptionException();

        $userSectorPermissionCollection = $this->createUserSectorPermissionCollection();

        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository(
            $userSectorPermissionCollection
        );

        $authenticationTokenEncoder = $this->createAuthenticationTokenEncoder(
            $encodedToken
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

        $sessionService->login(
            $sessionLoginParameters
        );
    }

    public function testIfLoginFailsByUnequalsPasswords(): void
    {
        $this->expectException(InvalidCredentialsException::class);

        $user = $this->createUser(
            Id::make(1),
            Username::make("test"),
            DecodedPassword::make("test"),
            true
        );

        $userRepository = $this->createUserRepository(
            $user
        );
        $encodedToken = $this->createEncodedToken("potato");
        $clock = $this->createClock("UTC");
        $now = $clock->now();
        $issuedAt = $now;
        $expiredAt = $now->modify("+1 hour");
        $decodedToken = $this->createDecodedToken(
            $user,
            $issuedAt,
            $expiredAt
        );

        $tokenCache = $this->createTokenCache(
            $encodedToken
        );
        $authenticationTokenDecoder = $this->createAuthenticationTokenDecoder(
            $decodedToken
        );
        $encrypter = $this->createEncrypter();

        $userSectorPermissionCollection = $this->createUserSectorPermissionCollection();

        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository(
            $userSectorPermissionCollection
        );

        $authenticationTokenEncoder = $this->createAuthenticationTokenEncoder(
            $encodedToken
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

        $userWithWrongPassword = $this->createUser(
            Id::make(1),
            Username::make("test"),
            DecodedPassword::make("wrongpassword"),
            true
        );
        $sessionLoginParameters = $this->createSessionLoginParameters($userWithWrongPassword, false);

        $sessionService->login(
            $sessionLoginParameters
        );
    }

    /*
    ----------------
    | Logoff Tests |
    ----------------
    */

    public function testIfLogoffSucceds(): void
    {
        $user = $this->createUser(
            Id::make(1),
            Username::make("test"),
            DecodedPassword::make("test"),
            true
        );

        $userRepository = $this->createUserRepository(
            $user
        );
        $encodedToken = $this->createEncodedToken("potato");
        $clock = $this->createClock("UTC");
        $now = $clock->now();
        $issuedAt = $now;
        $expiredAt = $now->modify("+1 hour");
        $decodedToken = $this->createDecodedToken(
            $user,
            $issuedAt,
            $expiredAt
        );

        $tokenCache = $this->createTokenCache(
            $encodedToken
        );
        $authenticationTokenDecoder = $this->createAuthenticationTokenDecoder(
            $decodedToken
        );
        $encrypter = $this->createEncrypter();

        $userSectorPermissionCollection = $this->createUserSectorPermissionCollection();

        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository(
            $userSectorPermissionCollection
        );

        $authenticationTokenEncoder = $this->createAuthenticationTokenEncoder(
            $encodedToken
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

        $hasLoggedOut = $sessionService->logoff(
            $encodedToken
        );

        $this->assertTrue($hasLoggedOut);
    }

    public function testIfLogoffFails(): void
    {
        $user = $this->createUser(
            Id::make(1),
            Username::make("test"),
            DecodedPassword::make("test"),
            true
        );

        $userRepository = $this->createUserRepository(
            $user
        );
        $encodedToken = $this->createEncodedToken("potato");
        $clock = $this->createClock("UTC");
        $now = $clock->now();
        $issuedAt = $now;
        $expiredAt = $now->modify("+1 hour");
        $decodedToken = $this->createDecodedToken(
            $user,
            $issuedAt,
            $expiredAt
        );

        $tokenCache = $this->createTokenCacheThatDoesNotDelete(
            $encodedToken
        );
        $authenticationTokenDecoder = $this->createAuthenticationTokenDecoder(
            $decodedToken
        );
        $encrypter = $this->createEncrypter();

        $userSectorPermissionCollection = $this->createUserSectorPermissionCollection();

        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository(
            $userSectorPermissionCollection
        );

        $authenticationTokenEncoder = $this->createAuthenticationTokenEncoder(
            $encodedToken
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

        $hasLoggedOut = $sessionService->logoff(
            $encodedToken
        );

        $this->assertFalse($hasLoggedOut);
    }

    /*
    -----------------------
    | Retrieve Data Tests |
    -----------------------
    */

    public function testIfRetrieveDataSucceeds(): void
    {
        $user = $this->createUser(
            Id::make(1),
            Username::make("test"),
            DecodedPassword::make("test"),
            true
        );

        $userRepository = $this->createUserRepository(
            $user
        );
        $encodedToken = $this->createEncodedToken("potato");
        $clock = $this->createClock("UTC");
        $now = $clock->now();
        $issuedAt = $now;
        $expiredAt = $now->modify("+1 hour");
        $decodedToken = $this->createDecodedToken(
            $user,
            $issuedAt,
            $expiredAt
        );

        $tokenCache = $this->createTokenCache(
            $encodedToken
        );
        $authenticationTokenDecoder = $this->createAuthenticationTokenDecoder(
            $decodedToken
        );
        $encrypter = $this->createEncrypter();

        $userSectorPermissionCollection = $this->createUserSectorPermissionCollection();

        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository(
            $userSectorPermissionCollection
        );

        $authenticationTokenEncoder = $this->createAuthenticationTokenEncoder(
            $encodedToken
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

        $sessionData = $sessionService->retrieveData(
            $encodedToken
        );

        $this->assertEquals(
            $user->getId()->getValue(),
            $sessionData->getUserId()->getValue()
        );
        $this->assertEquals(
            $user->getUsername()->getValue(),
            $sessionData->getUsername()->getValue()
        );
        $this->assertCount(
            $userSectorPermissionCollection->count(),
            $sessionData->getUserSectorPermissionCollection()->fetchAll()
        );
    }
}
