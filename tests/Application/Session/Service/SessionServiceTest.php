<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Tests\Application\Session\Service;

use Mvreisg\GamebaseBackend\Domain\Encryption\Interface\EncryptionInterface;
use Mvreisg\GamebaseBackend\Domain\Shared\Interface\ClockInterface;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\User\Entity\User;
use Mvreisg\GamebaseBackend\Domain\User\Repository\UserRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\User\ValueObject\Password\Decoded\DecodedPassword;
use Mvreisg\GamebaseBackend\Domain\User\ValueObject\Username\Username;
use Mvreisg\GamebaseBackend\Infrastructure\Time\Clock;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SessionServiceTest extends TestCase
{
    private function createClock(string $timezone): ClockInterface
    {
        $clock = new Clock(
            new \DateTimeZone(
                $timezone
            )
        );
        return $clock;
    }

    private function createUser(
        Id $id,
        Username $username,
        DecodedPassword $password,
        bool $isActive
    ): User {
        $user = new User(
            $id,
            $username,
            $password,
            $isActive
        );
        $user->setId($id);
        return $user;
    }

    private function createUserRepository(
        User $user
    ): MockObject&UserRepositoryInterface {
        $repository = $this->createMock(UserRepositoryInterface::class);
        $repository
            ->method("findByUsername")
            ->willReturn($user);

        return $repository;
    }

    private function createEncrypter(): MockObject&EncryptionInterface
    {
        $encrypter = $this->createMock(EncryptionInterface::class);
        $encrypter
            ->method("decrypt")
            ->willReturn("test");

        return $encrypter;
    }

    /*
    ---------------
    | Login Tests |
    ---------------
    */

    public function testIfLoginSucceds(): void
    {
        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
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
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
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
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
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
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
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
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
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
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("wrongpassword"),
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
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
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
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
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
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
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
