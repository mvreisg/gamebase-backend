<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Tests\Application\Authentication\Service;

use Mvreisg\GamebaseBackend\Application\Authentication\Data\AuthenticationData;
use Mvreisg\GamebaseBackend\Application\Authentication\Exception\InvalidTokenException;
use Mvreisg\GamebaseBackend\Application\Authentication\Exception\UnexistantTokenException;
use Mvreisg\GamebaseBackend\Application\Authentication\Services\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Authentication\Token\AuthenticationToken;
use Mvreisg\GamebaseBackend\Application\Authentication\Token\Cache\AuthenticationTokenCacheInterface;
use Mvreisg\GamebaseBackend\Application\Authentication\Token\Provider\AuthenticationTokenProvider;
use Mvreisg\GamebaseBackend\Application\Authentication\Token\Provider\Exception\AuthenticationTokenProviderException;
use Mvreisg\GamebaseBackend\Domain\Shared\Interface\ClockInterface;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\User\Entity\User;
use Mvreisg\GamebaseBackend\Domain\User\ValueObject\Password\Decoded\DecodedPassword;
use Mvreisg\GamebaseBackend\Domain\User\ValueObject\Username\Username;
use Mvreisg\GamebaseBackend\Infrastructure\Time\Clock;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AuthenticationServiceTest extends TestCase
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

    private function createEmptyAuthenticationTokenCache(
    ): MockObject&AuthenticationTokenCacheInterface {
        $cache = $this->createMock(AuthenticationTokenCacheInterface::class);
        $cache
            ->method("exists")
            ->willReturn(
                false
            );
        return $cache;
    }

    private function createAuthenticationTokenCache(
        string $token
    ): MockObject&AuthenticationTokenCacheInterface {
        $cache = $this->createMock(AuthenticationTokenCacheInterface::class);
        $cache
            ->method("exists")
            ->willReturn(true);
        $cache
            ->method("get")
            ->willReturn(
                $token
            );
        return $cache;
    }

    private function createAuthenticationTokenProvider(): MockObject&AuthenticationTokenProvider
    {
        $provider = $this->createMock(AuthenticationTokenProvider::class);
        return $provider;
    }

    private function createAuthenticationTokenProviderWithDecodeReturn(
        AuthenticationToken $token
    ): MockObject&AuthenticationTokenProvider {
        $provider = $this->createMock(AuthenticationTokenProvider::class);
        $provider
            ->method("decode")
            ->willReturn(
                $token
            );
        return $provider;
    }

    /**
     * @param AuthenticationToken[] $token
     */
    private function createAuthenticationTokenProviderWithSubsequentDecodeReturn(
        array $token
    ): MockObject&AuthenticationTokenProvider {
        $provider = $this->createMock(AuthenticationTokenProvider::class);
        $provider
            ->method("decode")
            ->willReturnOnConsecutiveCalls(
                ...$token
            );
        return $provider;
    }

    private function createAuthenticationTokenProviderWithEncodeException(): MockObject&AuthenticationTokenProvider
    {
        $provider = $this->createMock(AuthenticationTokenProvider::class);
        $provider
            ->method("encode")
            ->willThrowException(
                new AuthenticationTokenProviderException("encoding error.")
            );
        return $provider;
    }

    private function createAuthenticationTokenProviderWithDecodeException(): MockObject&AuthenticationTokenProvider
    {
        $provider = $this->createMock(AuthenticationTokenProvider::class);
        $provider
            ->method("decode")
            ->willThrowException(
                new AuthenticationTokenProviderException("decoding error.")
            );
        return $provider;
    }

    private function createAuthenticationTokenProviderWithValidationException(): MockObject&AuthenticationTokenProvider
    {
        $provider = $this->createMock(AuthenticationTokenProvider::class);
        $provider
            ->method("validate")
            ->willThrowException(
                new AuthenticationTokenProviderException("validation error.")
            );
        return $provider;
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

    private function createUser(
        Id $id,
        Username $username,
        DecodedPassword $password,
        bool $isActive
    ): User {
        return new User(
            $id,
            $username,
            $password,
            $isActive
        );
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

    /*
    ----------------
    | Encode Tests |
    ----------------
    */

    public function testIfAValidEncodeSucceds(): void
    {
        $this->expectNotToPerformAssertions();

        $user = $this->createUser(
            Id::create(1),
            Username::create("marcus"),
            DecodedPassword::create("password123"),
            true
        );
        $tokenCache = $this->createEmptyAuthenticationTokenCache();
        $tokenProvider = $this->createAuthenticationTokenProvider();
        $authenticationService = $this->createAuthenticationService(
            $tokenCache,
            $tokenProvider
        );
        $authenticationData = $this->createAuthenticationData(
            $user->getId(),
            $user->getUsername()
        );
        $interval = new \DateInterval("P1D");
        $authenticationService->encode(
            $authenticationData,
            $interval
        );
    }

    public function testIfAEncodingFails(): void
    {
        $this->expectException(AuthenticationTokenProviderException::class);

        $user = $this->createUser(
            Id::create(1),
            Username::create("marcus"),
            DecodedPassword::create("password123"),
            true
        );
        $tokenCache = $this->createEmptyAuthenticationTokenCache();
        $tokenProvider = $this->createAuthenticationTokenProviderWithEncodeException();
        $authenticationService = $this->createAuthenticationService(
            $tokenCache,
            $tokenProvider
        );
        $authenticationData = $this->createAuthenticationData(
            $user->getId(),
            $user->getUsername()
        );
        $interval = new \DateInterval("P1D");
        $authenticationService->encode(
            $authenticationData,
            $interval
        );
    }

    /*
    ----------------
    | Decode Tests |
    ----------------
    */

    public function testIfAValidDecodeSucceds(): void
    {
        $this->expectNotToPerformAssertions();

        $token = "potato";
        $tokenCache = $this->createEmptyAuthenticationTokenCache();
        $tokenProvider = $this->createAuthenticationTokenProvider();
        $authenticationService = $this->createAuthenticationService(
            $tokenCache,
            $tokenProvider
        );
        $authenticationService->decode(
            $token
        );
    }

    public function testIfDecodingFails(): void
    {
        $this->expectException(AuthenticationTokenProviderException::class);

        $token = "potato";
        $tokenCache = $this->createEmptyAuthenticationTokenCache();
        $tokenProvider = $this->createAuthenticationTokenProviderWithDecodeException();
        $authenticationService = $this->createAuthenticationService(
            $tokenCache,
            $tokenProvider
        );
        $authenticationService->decode(
            $token
        );
    }

    /*
    --------------------
    | Validation Tests |
    --------------------
    */

    public function testIfAValidTokenGetsValidated(): void
    {
        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            true
        );
        $authenticationData = new AuthenticationData(
            $user->getId(),
            $user->getUsername()
        );
        $clock = $this->createClock("UTC");
        $issuedAt = $clock->now();
        $expiresAt = $issuedAt->modify("+1 day");
        $token = $this->createAuthenticationToken(
            $authenticationData,
            $issuedAt,
            $expiresAt
        );
        $stringToken = "potato";
        $tokenCache = $this->createAuthenticationTokenCache(
            $stringToken
        );
        $tokenProvider = $this->createAuthenticationTokenProviderWithDecodeReturn(
            $token
        );
        $authenticationService = $this->createAuthenticationService(
            $tokenCache,
            $tokenProvider
        );
        $decodedToken = $authenticationService->validate(
            $stringToken
        );

        $this->assertEquals(
            $token->getIssuedAt()->getTimestamp(),
            $decodedToken->getIssuedAt()->getTimestamp(),
        );

        $this->assertEquals(
            $token->getExpiresAt()->getTimestamp(),
            $decodedToken->getExpiresAt()->getTimestamp(),
        );

        $this->assertEquals(
            $token->getAuthenticationData()->getUserId()->getValue(),
            $decodedToken->getAuthenticationData()->getUserId()->getValue(),
        );

        $this->assertEquals(
            $token->getAuthenticationData()->getUsername()->getValue(),
            $decodedToken->getAuthenticationData()->getUsername()->getValue(),
        );
    }

    public function testIfAInvalidTokenGetsUnauthorizedByExpiration(): void
    {
        $this->expectException(AuthenticationTokenProviderException::class);

        $stringToken = "potato";
        $tokenCache = $this->createAuthenticationTokenCache(
            $stringToken
        );
        $tokenProvider = $this->createAuthenticationTokenProviderWithValidationException();
        $authenticationService = $this->createAuthenticationService(
            $tokenCache,
            $tokenProvider
        );
        $authenticationService->validate(
            $stringToken
        );
    }

    public function testIfAInvalidTokenGetsUnauthorizedByFutureIssueDate(): void
    {
        $this->expectException(AuthenticationTokenProviderException::class);

        $stringToken = "potato";
        $tokenCache = $this->createAuthenticationTokenCache(
            $stringToken
        );
        $tokenProvider = $this->createAuthenticationTokenProviderWithValidationException();
        $authenticationService = $this->createAuthenticationService(
            $tokenCache,
            $tokenProvider
        );
        $authenticationService->validate(
            $stringToken
        );
    }

    public function testIfAInformedButUnexistantInCacheTokenGetsUnauthorized(): void
    {
        $this->expectException(UnexistantTokenException::class);

        $token = "potato";
        $tokenCache = $this->createEmptyAuthenticationTokenCache();
        $tokenProvider = $this->createAuthenticationTokenProvider();
        $authenticationService = $this->createAuthenticationService(
            $tokenCache,
            $tokenProvider
        );
        $authenticationService->validate(
            $token
        );
    }

    public function testIfAInformedTokenIsUnauthorizedBecauseOfDifferentTokenStringValues(): void
    {
        $this->expectException(InvalidTokenException::class);

        $token = "potato";
        $tokenCache = $this->createAuthenticationTokenCache(
            $token
        );
        $tokenProvider = $this->createAuthenticationTokenProvider();
        $authenticationService = $this->createAuthenticationService(
            $tokenCache,
            $tokenProvider
        );
        $authenticationService->validate(
            "potato2"
        );
    }

    public function testIfAInformedTokenIsUnauthorizedBecauseOfDifferentUserIdValues(): void
    {
        $this->expectException(InvalidTokenException::class);

        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            true
        );
        $authenticationData = new AuthenticationData(
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
        $encodedToken = "potato";
        $tokenCache = $this->createAuthenticationTokenCache(
            $encodedToken
        );
        $tokenProvider = $this->createAuthenticationTokenProviderWithDecodeReturn(
            $decodedToken
        );
        $authenticationService = $this->createAuthenticationService(
            $tokenCache,
            $tokenProvider
        );
        $authenticationService->validate(
            "fail"
        );
    }

    public function testIfAInformedTokenIsUnauthorizedBecauseOfDifferentUsernameValues(): void
    {
        $this->expectException(InvalidTokenException::class);

        $firstUser = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            true
        );
        $secondUser = $this->createUser(
            Id::create(2),
            Username::create("test2"),
            DecodedPassword::create("test2"),
            true
        );
        $firstAuthenticationData = new AuthenticationData(
            $firstUser->getId(),
            $firstUser->getUsername()
        );
        $secondAuthenticationData = new AuthenticationData(
            $secondUser->getId(),
            $secondUser->getUsername()
        );
        $clock = $this->createClock("UTC");
        $issuedAt = $clock->now();
        $expiresAt = $issuedAt->modify("+1 day");
        $firstDecodedToken = $this->createAuthenticationToken(
            $firstAuthenticationData,
            $issuedAt,
            $expiresAt
        );

        $secondDecodedToken = $this->createAuthenticationToken(
            $secondAuthenticationData,
            $issuedAt,
            $expiresAt
        );

        $encodedToken = "potato";
        $tokenCache = $this->createAuthenticationTokenCache(
            $encodedToken
        );
        $tokenProvider = $this->createAuthenticationTokenProviderWithSubsequentDecodeReturn([
            $firstDecodedToken,
            $secondDecodedToken
        ]);
        $authenticationService = $this->createAuthenticationService(
            $tokenCache,
            $tokenProvider
        );
        $authenticationService->validate(
            $encodedToken
        );
    }
}
