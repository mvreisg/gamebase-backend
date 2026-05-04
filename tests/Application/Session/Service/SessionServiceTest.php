<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Tests\Application\Session\Service;

use Mvreisg\GamebaseBackend\Application\Authentication\Data\AuthenticationData;
use Mvreisg\GamebaseBackend\Application\Authentication\Services\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Authentication\Token\AuthenticationToken;
use Mvreisg\GamebaseBackend\Application\Authentication\Token\Cache\AuthenticationTokenCacheInterface;
use Mvreisg\GamebaseBackend\Application\Authentication\Token\Provider\AuthenticationTokenProvider;
use Mvreisg\GamebaseBackend\Application\Session\Exception\InvalidCredentialsException;
use Mvreisg\GamebaseBackend\Application\Session\Exception\UnexistantUserException;
use Mvreisg\GamebaseBackend\Application\Session\Login\Parameters\SessionLoginParameters;
use Mvreisg\GamebaseBackend\Application\Session\Service\SessionService;
use Mvreisg\GamebaseBackend\Domain\Encryption\Interface\EncryptionInterface;
use Mvreisg\GamebaseBackend\Domain\Encryption\Interface\Exception\EncryptionInterfaceException;
use Mvreisg\GamebaseBackend\Domain\Shared\Interface\ClockInterface;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\User\Entity\User;
use Mvreisg\GamebaseBackend\Domain\User\Repository\UserRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\User\ValueObject\Password\Decoded\DecodedPassword;
use Mvreisg\GamebaseBackend\Domain\User\ValueObject\Password\Password;
use Mvreisg\GamebaseBackend\Domain\User\ValueObject\Username\Username;
use Mvreisg\GamebaseBackend\Domain\UserSectorPermission\Repository\UserSectorPermissionRepositoryInterface;
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

    private function createAuthenticationToken(
        AuthenticationData $data,
        \DateTimeImmutable $issuedAt,
        \DateTimeImmutable $expiresAt,
    ): MockObject&AuthenticationToken {
        $token = $this->createMock(AuthenticationToken::class);

        $token
            ->method("getIssuedAt")
            ->willReturn($issuedAt);

        $token
            ->method("getExpiresAt")
            ->willReturn($expiresAt);

        $token
            ->method("getAuthenticationData")
            ->willReturn($data);

        return $token;
    }

    private function createAuthenticationData(
        Id $id,
        Username $username
    ): AuthenticationData {
        $authenticationData = new AuthenticationData(
            $id,
            $username
        );
        return $authenticationData;
    }

    private function createUser(
        Id $id,
        Username $username,
        DecodedPassword $password,
        bool $isActive
    ): User {
        $user = User::create(
            $id,
            $username,
            $password,
            $isActive
        );
        return $user;
    }

    private function createEmptyUserRepository(): MockObject&UserRepositoryInterface
    {
        $repository = $this->createMock(UserRepositoryInterface::class);
        $repository
            ->method("findByUsername")
            ->willReturn(
                null
            );

        return $repository;
    }

    private function createUserRepository(
        User $user
    ): MockObject&UserRepositoryInterface {
        $repository = $this->createMock(UserRepositoryInterface::class);
        $repository
            ->method("findByUsername")
            ->willReturn(
                $user
            );

        return $repository;
    }

    private function createUserSectorPermissionRepository(): MockObject&UserSectorPermissionRepositoryInterface
    {
        $repository = $this->createMock(UserSectorPermissionRepositoryInterface::class);
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

    private function createEncrypterWithDecryptionException(): MockObject&EncryptionInterface
    {
        $encrypter = $this->createMock(EncryptionInterface::class);
        $encrypter
            ->method("decrypt")
            ->willThrowException(
                new EncryptionInterfaceException(
                    "decrypt error."
                )
            );

        return $encrypter;
    }

    private function createAuthenticationTokenCache(
        bool $exists,
        string $token,
        bool $delete
    ): MockObject&AuthenticationTokenCacheInterface {
        $cache = $this->createMock(AuthenticationTokenCacheInterface::class);
        $cache
            ->method("exists")
            ->willReturn(
                $exists
            );
        $cache
            ->method("get")
            ->willReturn(
                $token
            );
        $cache
            ->method("delete")
            ->willReturn(
                $delete
            );
        return $cache;
    }

    private function createAuthenticationService(
        MockObject&AuthenticationTokenCacheInterface $tokenCache,
        MockObject&AuthenticationTokenProvider $tokenProvider
    ): AuthenticationService {
        return new AuthenticationService(
            $tokenCache,
            $tokenProvider,
        );
    }

    private function createAuthenticationTokenProvider(
        string $token
    ): MockObject&AuthenticationTokenProvider {
        $provider = $this->createMock(AuthenticationTokenProvider::class);
        $provider
            ->method("encode")
            ->willReturn(
                $token
            );

        return $provider;
    }

    private function createAuthenticationTokenProviderWithDecodeReturn(
        string $encodedToken,
        AuthenticationToken $decodedToken
    ): MockObject&AuthenticationTokenProvider {
        $provider = $this->createMock(AuthenticationTokenProvider::class);
        $provider
            ->method("encode")
            ->willReturn(
                $encodedToken
            );

        $provider
            ->method("decode")
            ->willReturn(
                $decodedToken
            );

        return $provider;
    }

    private function createSessionService(
        MockObject&UserRepositoryInterface $userRepository,
        MockObject&UserSectorPermissionRepositoryInterface $userSectorPermissionRepository,
        MockObject&EncryptionInterface $encrypter,
        AuthenticationService $authenticationService,
        MockObject&AuthenticationTokenCacheInterface $authenticationTokenCache
    ): SessionService {
        $service = new SessionService(
            $userRepository,
            $userSectorPermissionRepository,
            $encrypter,
            $authenticationService,
            $authenticationTokenCache
        );
        return $service;
    }

    private function createSessionLoginParameters(
        Username $username,
        Password $password,
        bool $oneWeekLogin
    ): SessionLoginParameters {
        return new SessionLoginParameters(
            $username,
            $password,
            $oneWeekLogin
        );
    }

    /*
    ---------------
    | Login Tests |
    ---------------
    */

    public function testIfLoginSucceeds(): void
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
        $encodedToken = "potato";
        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository();
        $tokenCache = $this->createAuthenticationTokenCache(
            true,
            $encodedToken,
            true,
        );
        $clock = $this->createClock("UTC");
        $issuedAt = $clock->now();
        $expiresAt = $issuedAt->modify("+1 day");
        $authenticationData = $this->createAuthenticationData(
            $user->getId(),
            $user->getUsername()
        );
        $decodedToken = $this->createAuthenticationToken(
            $authenticationData,
            $issuedAt,
            $expiresAt
        );
        $encrypter = $this->createEncrypter();
        $tokenProvider = $this->createAuthenticationTokenProviderWithDecodeReturn(
            $encodedToken,
            $decodedToken
        );
        $authenticationService = $this->createAuthenticationService(
            $tokenCache,
            $tokenProvider
        );
        $sessionService = $this->createSessionService(
            $userRepository,
            $userSectorPermissionRepository,
            $encrypter,
            $authenticationService,
            $tokenCache
        );

        $sessionLoginParameters = $this->createSessionLoginParameters(
            $user->getUsername(),
            $user->getPassword(),
            false
        );

        $result = $sessionService->login(
            $sessionLoginParameters
        );

        $this->assertEquals(
            $encodedToken,
            $result->getToken()
        );

        $this->assertEquals(
            $user->getId()->getValue(),
            $result->getData()->getUserId()->getValue()
        );

        $this->assertEquals(
            $user->getUsername()->getValue(),
            $result->getData()->getUsername()->getValue()
        );

        $this->assertCount(
            0,
            $result->getData()->getUserSectorPermissionCollection()->fetchAll()
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
        $encodedToken = "potato";
        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository();
        $tokenCache = $this->createAuthenticationTokenCache(
            true,
            $encodedToken,
            true,
        );
        $encrypter = $this->createEncrypter();
        $clock = $this->createClock("UTC");
        $issuedAt = $clock->now();
        $expiresAt = $issuedAt->modify("+1 day");
        $authenticationData = $this->createAuthenticationData(
            $user->getId(),
            $user->getUsername()
        );
        $decodedToken = $this->createAuthenticationToken(
            $authenticationData,
            $issuedAt,
            $expiresAt
        );
        $tokenProvider = $this->createAuthenticationTokenProviderWithDecodeReturn(
            $encodedToken,
            $decodedToken
        );
        $authenticationService = $this->createAuthenticationService(
            $tokenCache,
            $tokenProvider
        );
        $sessionService = $this->createSessionService(
            $userRepository,
            $userSectorPermissionRepository,
            $encrypter,
            $authenticationService,
            $tokenCache
        );

        $sessionLoginParameters = $this->createSessionLoginParameters(
            $user->getUsername(),
            $user->getPassword(),
            false
        );

        $result = $sessionService->login(
            $sessionLoginParameters
        );

        $this->assertEquals(
            $encodedToken,
            $result->getToken()
        );

        $this->assertEquals(
            $user->getId()->getValue(),
            $result->getData()->getUserId()->getValue()
        );

        $this->assertEquals(
            $user->getUsername()->getValue(),
            $result->getData()->getUsername()->getValue()
        );

        $this->assertCount(
            0,
            $result->getData()->getUserSectorPermissionCollection()->fetchAll()
        );

        $result = $sessionService->login(
            $sessionLoginParameters
        );

        $this->assertEquals(
            $encodedToken,
            $result->getToken()
        );

        $this->assertEquals(
            $user->getId()->getValue(),
            $result->getData()->getUserId()->getValue()
        );

        $this->assertEquals(
            $user->getUsername()->getValue(),
            $result->getData()->getUsername()->getValue()
        );

        $this->assertCount(
            0,
            $result->getData()->getUserSectorPermissionCollection()->fetchAll()
        );
    }

    public function testIfLoginFailsByUnexistantUserIdentifiedByUsernameOnRepository(): void
    {
        $this->expectException(UnexistantUserException::class);

        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            true
        );

        $userRepository = $this->createEmptyUserRepository();
        $encodedToken = "potato";
        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository();
        $tokenCache = $this->createAuthenticationTokenCache(
            true,
            $encodedToken,
            true,
        );
        $encrypter = $this->createEncrypter();
        $tokenProvider = $this->createAuthenticationTokenProvider(
            $encodedToken
        );
        $authenticationService = $this->createAuthenticationService(
            $tokenCache,
            $tokenProvider
        );
        $sessionService = $this->createSessionService(
            $userRepository,
            $userSectorPermissionRepository,
            $encrypter,
            $authenticationService,
            $tokenCache
        );

        $sessionLoginParameters = $this->createSessionLoginParameters(
            $user->getUsername(),
            $user->getPassword(),
            false
        );

        $sessionService->login(
            $sessionLoginParameters
        );
    }

    public function testIfLoginFailsByDecryptionException(): void
    {
        $this->expectException(EncryptionInterfaceException::class);

        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            true
        );

        $userRepository = $this->createUserRepository(
            $user
        );
        $encodedToken = "potato";
        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository();
        $tokenCache = $this->createAuthenticationTokenCache(
            true,
            $encodedToken,
            true,
        );
        $encrypter = $this->createEncrypterWithDecryptionException();
        $tokenProvider = $this->createAuthenticationTokenProvider(
            $encodedToken
        );
        $authenticationService = $this->createAuthenticationService(
            $tokenCache,
            $tokenProvider
        );
        $sessionService = $this->createSessionService(
            $userRepository,
            $userSectorPermissionRepository,
            $encrypter,
            $authenticationService,
            $tokenCache
        );

        $sessionLoginParameters = $this->createSessionLoginParameters(
            $user->getUsername(),
            $user->getPassword(),
            false
        );

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
        $encodedToken = "potato";
        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository();
        $tokenCache = $this->createAuthenticationTokenCache(
            true,
            $encodedToken,
            true,
        );
        $encrypter = $this->createEncrypter();
        $tokenProvider = $this->createAuthenticationTokenProvider(
            $encodedToken
        );
        $authenticationService = $this->createAuthenticationService(
            $tokenCache,
            $tokenProvider
        );
        $sessionService = $this->createSessionService(
            $userRepository,
            $userSectorPermissionRepository,
            $encrypter,
            $authenticationService,
            $tokenCache
        );

        $sessionLoginParameters = $this->createSessionLoginParameters(
            $user->getUsername(),
            DecodedPassword::create("error"),
            false
        );

        $sessionService->login(
            $sessionLoginParameters
        );
    }

    /*
    ----------------
    | Logoff Tests |
    ----------------
    */

    public function testIfLogoffSucceeds(): void
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
        $encodedToken = "potato";
        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository();
        $tokenCache = $this->createAuthenticationTokenCache(
            true,
            $encodedToken,
            true,
        );
        $encrypter = $this->createEncrypter();
        $authenticationData = $this->createAuthenticationData(
            $user->getId(),
            $user->getUsername()
        );
        $clock = $this->createClock("UTC");
        $issuedAt = $clock->now();
        $expiresAt = $issuedAt->modify("+1 day");
        $decodedToken = $this->createAuthenticationToken(
            $authenticationData,
            $issuedAt,
            $expiresAt
        );
        $tokenProvider = $this->createAuthenticationTokenProviderWithDecodeReturn(
            $encodedToken,
            $decodedToken
        );
        $authenticationService = $this->createAuthenticationService(
            $tokenCache,
            $tokenProvider
        );
        $sessionService = $this->createSessionService(
            $userRepository,
            $userSectorPermissionRepository,
            $encrypter,
            $authenticationService,
            $tokenCache
        );

        $loggedOut = $sessionService->logoff(
            $encodedToken
        );

        $this->assertTrue(
            $loggedOut
        );
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
        $encodedToken = "potato";
        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository();
        $tokenCache = $this->createAuthenticationTokenCache(
            true,
            $encodedToken,
            false,
        );
        $encrypter = $this->createEncrypter();
        $authenticationData = $this->createAuthenticationData(
            $user->getId(),
            $user->getUsername()
        );
        $clock = $this->createClock("UTC");
        $issuedAt = $clock->now();
        $expiresAt = $issuedAt->modify("+1 day");
        $decodedToken = $this->createAuthenticationToken(
            $authenticationData,
            $issuedAt,
            $expiresAt
        );
        $tokenProvider = $this->createAuthenticationTokenProviderWithDecodeReturn(
            $encodedToken,
            $decodedToken
        );
        $authenticationService = $this->createAuthenticationService(
            $tokenCache,
            $tokenProvider
        );
        $sessionService = $this->createSessionService(
            $userRepository,
            $userSectorPermissionRepository,
            $encrypter,
            $authenticationService,
            $tokenCache
        );

        $loggedOut = $sessionService->logoff(
            $encodedToken
        );

        $this->assertFalse(
            $loggedOut
        );
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
        $encodedToken = "potato";
        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository();
        $tokenCache = $this->createAuthenticationTokenCache(
            true,
            $encodedToken,
            false,
        );
        $encrypter = $this->createEncrypter();
        $authenticationData = $this->createAuthenticationData(
            $user->getId(),
            $user->getUsername()
        );
        $clock = $this->createClock("UTC");
        $issuedAt = $clock->now();
        $expiresAt = $issuedAt->modify("+1 day");
        $decodedToken = $this->createAuthenticationToken(
            $authenticationData,
            $issuedAt,
            $expiresAt
        );
        $tokenProvider = $this->createAuthenticationTokenProviderWithDecodeReturn(
            $encodedToken,
            $decodedToken
        );
        $authenticationService = $this->createAuthenticationService(
            $tokenCache,
            $tokenProvider
        );
        $sessionService = $this->createSessionService(
            $userRepository,
            $userSectorPermissionRepository,
            $encrypter,
            $authenticationService,
            $tokenCache
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
            0,
            $sessionData->getUserSectorPermissionCollection()->fetchAll()
        );
    }
}
