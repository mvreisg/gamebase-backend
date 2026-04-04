<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Tests\Application\Services;

use Mvreisg\GamebaseBackend\Application\Services\Authentication\AuthenticationService;
use Mvreisg\GamebaseBackend\Domain\Authentication\Data\AuthenticationData;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Decoder\AuthenticationTokenDecoder;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Decoder\Exception\AuthenticationTokenDecoderException;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Encoder\AuthenticationTokenEncoder;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Encoder\Exception\AuthenticationTokenEncoderException;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Validate\AuthenticationTokenValidator;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Validate\Exception\AuthenticationTokenValidatorException;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Data\Decoded\DecodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Data\Encoded\EncodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Authorization\Exception\UnauthorizedException;
use Mvreisg\GamebaseBackend\Domain\Cache\Token\Interface\TokenCacheInterface;
use Mvreisg\GamebaseBackend\Domain\Entities\Clock;
use Mvreisg\GamebaseBackend\Domain\Entities\DecodedPassword;
use Mvreisg\GamebaseBackend\Domain\Entities\Id;
use Mvreisg\GamebaseBackend\Domain\Entities\User;
use Mvreisg\GamebaseBackend\Domain\Entities\Username;
use Mvreisg\GamebaseBackend\Domain\Interfaces\ClockInterface;
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

    private function createEncodedToken(
        string $token
    ): MockObject&\Mvreisg\GamebaseBackend\Domain\Authentication\Token\Data\Encoded\EncodedAuthenticationToken {
        $encodedToken = $this->createMock(EncodedAuthenticationToken::class);
        $encodedToken
            ->method("getToken")
            ->willReturn($token);
        return $encodedToken;
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

    private function createEmptyTokenCache(
    ): MockObject&\Mvreisg\GamebaseBackend\Domain\Cache\Token\Interface\TokenCacheInterface {
        $tokenCache = $this->createMock(TokenCacheInterface::class);
        return $tokenCache;
    }

    private function createTokenCache(
        MockObject&\Mvreisg\GamebaseBackend\Domain\Authentication\Token\Data\Encoded\EncodedAuthenticationToken $token
    ): MockObject&\Mvreisg\GamebaseBackend\Domain\Cache\Token\Interface\TokenCacheInterface {
        $tokenCache = $this->createMock(TokenCacheInterface::class);
        $tokenCache
            ->method("exists")
            ->willReturn(true);
        $tokenCache
            ->method("get")
            ->willReturn(
                $token
            );
        return $tokenCache;
    }

    private function createAuthenticationTokenDecoder(
    ): MockObject&\Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Decoder\AuthenticationTokenDecoder {
        $authenticationTokenDecoder = $this->createMock(AuthenticationTokenDecoder::class);
        return $authenticationTokenDecoder;
    }

    private function createAuthenticationTokenDecoderWithDecodeException(
    ): MockObject&\Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Decoder\AuthenticationTokenDecoder {
        $authenticationTokenDecoder = $this->createMock(AuthenticationTokenDecoder::class);
        $authenticationTokenDecoder
            ->method("decode")
            ->willThrowException(
                new AuthenticationTokenDecoderException(
                    "Decoding failed."
                )
            );
        return $authenticationTokenDecoder;
    }

    private function createAuthenticationTokenDecoderWithReturn(
        MockObject&\Mvreisg\GamebaseBackend\Domain\Authentication\Token\Data\Decoded\DecodedAuthenticationToken $decodedToken
    ): MockObject&\Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Decoder\AuthenticationTokenDecoder {
        $authenticationTokenDecoder = $this->createMock(AuthenticationTokenDecoder::class);
        $authenticationTokenDecoder
            ->method("decode")
            ->willReturn(
                $decodedToken
            );
        return $authenticationTokenDecoder;
    }

    /**
     * @param MockObject&\Mvreisg\GamebaseBackend\Domain\Authentication\Token\Data\Decoded\DecodedAuthenticationToken[] $decodedTokens
     */
    private function createAuthenticationTokenDecoderWithReturnOnConsecutiveCalls(
        array $decodedTokens
    ): MockObject&\Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Decoder\AuthenticationTokenDecoder {
        $authenticationTokenDecoder = $this->createMock(AuthenticationTokenDecoder::class);
        $authenticationTokenDecoder
            ->method("decode")
            ->willReturnOnConsecutiveCalls(
                ...$decodedTokens
            );
        return $authenticationTokenDecoder;
    }

    private function createAuthenticationTokenEncoder(): MockObject&\Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Encoder\AuthenticationTokenEncoder
    {
        $authenticationTokenEncoder = $this->createMock(AuthenticationTokenEncoder::class);
        return $authenticationTokenEncoder;
    }

    private function createAuthenticationTokenEncoderWithEncodeException(): MockObject&\Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Encoder\AuthenticationTokenEncoder
    {
        $authenticationTokenEncoder = $this->createMock(AuthenticationTokenEncoder::class);
        $authenticationTokenEncoder
            ->method("encode")
            ->willThrowException(
                new AuthenticationTokenEncoderException(
                    "Encoding failed."
                )
            );
        return $authenticationTokenEncoder;
    }

    private function createAuthenticationTokenValidator(
        ClockInterface $clock
    ): AuthenticationTokenValidator {
        $authenticationTokenValidator = new AuthenticationTokenValidator(
            $clock
        );
        return $authenticationTokenValidator;
    }

    private function createAuthenticationService(
        MockObject&\Mvreisg\GamebaseBackend\Domain\Cache\Token\Interface\TokenCacheInterface $tokenCache,
        MockObject&\Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Decoder\AuthenticationTokenDecoder $authenticationTokenDecoder,
        MockObject&\Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Encoder\AuthenticationTokenEncoder $authenticationTokenEncoder,
        AuthenticationTokenValidator $authenticationTokenValidator
    ): AuthenticationService {
        return new AuthenticationService(
            $tokenCache,
            $authenticationTokenDecoder,
            $authenticationTokenEncoder,
            $authenticationTokenValidator
        );
    }

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
            DecodedPassword::make("password123"),
            true
        );
        $tokenCache = $this->createEmptyTokenCache();
        $authenticationTokenDecoder = $this->createAuthenticationTokenDecoder();
        $clock = $this->createClock("UTC");
        $authenticationTokenValidator = $this->createAuthenticationTokenValidator(
            $clock
        );
        $authenticationTokenEncoder = $this->createAuthenticationTokenEncoder();
        $authenticationService = $this->createAuthenticationService(
            $tokenCache,
            $authenticationTokenDecoder,
            $authenticationTokenEncoder,
            $authenticationTokenValidator
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
        $this->expectException(AuthenticationTokenEncoderException::class);

        $user = $this->createUser(
            Id::create(1),
            Username::create("marcus"),
            DecodedPassword::make("password123"),
            true
        );
        $tokenCache = $this->createEmptyTokenCache();
        $authenticationTokenDecoder = $this->createAuthenticationTokenDecoder();
        $clock = $this->createClock("UTC");
        $authenticationTokenValidator = $this->createAuthenticationTokenValidator(
            $clock
        );
        $authenticationTokenEncoder = $this->createAuthenticationTokenEncoderWithEncodeException();
        $authenticationService = $this->createAuthenticationService(
            $tokenCache,
            $authenticationTokenDecoder,
            $authenticationTokenEncoder,
            $authenticationTokenValidator
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

        $tokenCache = $this->createEmptyTokenCache();
        $authenticationTokenDecoder = $this->createAuthenticationTokenDecoder();
        $clock = $this->createClock("UTC");
        $authenticationTokenValidator = $this->createAuthenticationTokenValidator(
            $clock
        );
        $authenticationTokenEncoder = $this->createAuthenticationTokenEncoder();
        $authenticationService = $this->createAuthenticationService(
            $tokenCache,
            $authenticationTokenDecoder,
            $authenticationTokenEncoder,
            $authenticationTokenValidator
        );
        $encodedToken = $this->createEncodedToken("potato");
        $authenticationService->decode(
            $encodedToken
        );
    }

    public function testIfDecodingFails(): void
    {
        $this->expectException(AuthenticationTokenDecoderException::class);

        $tokenCache = $this->createEmptyTokenCache();
        $authenticationTokenDecoder = $this->createAuthenticationTokenDecoderWithDecodeException();
        $clock = $this->createClock("UTC");
        $authenticationTokenValidator = $this->createAuthenticationTokenValidator(
            $clock
        );
        $authenticationTokenEncoder = $this->createAuthenticationTokenEncoder();
        $authenticationService = $this->createAuthenticationService(
            $tokenCache,
            $authenticationTokenDecoder,
            $authenticationTokenEncoder,
            $authenticationTokenValidator
        );
        $encodedToken = $this->createEncodedToken("potato");
        $authenticationService->decode(
            $encodedToken
        );
    }

    /*
    --------------------
    | Validation Tests |
    --------------------
    */

    public function testIfAValidTokenGetsValidated(): void
    {
        $id = Id::create(1);
        $username = Username::create("marcus");
        $password = DecodedPassword::make("password");
        $isActive = true;
        $user = $this->createUser(
            $id,
            $username,
            $password,
            $isActive
        );
        $encodedToken = $this->createEncodedToken(
            "potato"
        );
        $tokenCache = $this->createTokenCache(
            $encodedToken
        );
        $clock = $this->createClock("UTC");
        $now = $clock->now();
        $issuedAt = $now;
        $expiresAt = $now->modify("+1 hour");
        $decodedToken = $this->createDecodedToken(
            $user,
            $issuedAt,
            $expiresAt
        );
        $authenticationTokenEncoder = $this->createAuthenticationTokenEncoder();
        $authenticationService = $this->createAuthenticationService(
            $tokenCache,
            $this->createAuthenticationTokenDecoderWithReturn(
                $decodedToken
            ),
            $authenticationTokenEncoder,
            $this->createAuthenticationTokenValidator(
                $clock
            )
        );

        $return = $authenticationService->validate(
            $encodedToken
        );

        $this->assertEquals(
            $id->getValue(),
            $return->getUserId()->getValue()
        );

        $this->assertEquals(
            $username->getValue(),
            $return->getUsername()->getValue()
        );

        $this->assertEquals(
            $issuedAt->getTimestamp(),
            $return->getIssuedAt()->getTimestamp()
        );

        $this->assertEquals(
            $expiresAt->getTimestamp(),
            $return->getExpiresAt()->getTimestamp()
        );
    }

    public function testIfAInvalidTokenGetsUnauthorizedByExpiration(): void
    {
        $this->expectException(AuthenticationTokenValidatorException::class);

        $user = $this->createUser(
            Id::create(1),
            Username::create("marcus"),
            DecodedPassword::make("password123"),
            true
        );
        $encodedToken = $this->createEncodedToken("potato");
        $clock = $this->createClock("UTC");
        $now = $clock->now();
        $decodedToken = $this->createDecodedToken(
            $user,
            $now->modify("-1 hour"),
            $now->modify("-2 hour")
        );
        $tokenCache = $this->createTokenCache(
            $encodedToken
        );
        $authenticationTokenEncoder = $this->createAuthenticationTokenEncoder();
        $authenticationService = $this->createAuthenticationService(
            $tokenCache,
            $this->createAuthenticationTokenDecoderWithReturn(
                $decodedToken
            ),
            $authenticationTokenEncoder,
            $this->createAuthenticationTokenValidator(
                $clock
            )
        );

        $authenticationService->validate(
            $encodedToken
        );
    }

    public function testIfAInvalidTokenGetsUnauthorizedByFutureIssueDate(): void
    {
        $this->expectException(AuthenticationTokenValidatorException::class);

        $user = $this->createUser(
            Id::create(1),
            Username::create("marcus"),
            DecodedPassword::make("password123"),
            true
        );
        $encodedToken = $this->createEncodedToken("potato");
        $clock = $this->createClock("UTC");
        $now = $clock->now();
        $decodedToken = $this->createDecodedToken(
            $user,
            $now->modify("+1 hour"),
            $now
        );
        $tokenCache = $this->createTokenCache(
            $encodedToken
        );
        $authenticationTokenEncoder = $this->createAuthenticationTokenEncoder();
        $authenticationService = $this->createAuthenticationService(
            $tokenCache,
            $this->createAuthenticationTokenDecoderWithReturn(
                $decodedToken
            ),
            $authenticationTokenEncoder,
            $this->createAuthenticationTokenValidator(
                $clock
            )
        );

        $authenticationService->validate(
            $encodedToken
        );
    }

    public function testIfAInformedButUnexistantInCacheTokenGetsUnauthorized(): void
    {
        $this->expectException(UnauthorizedException::class);

        $user = $this->createUser(
            Id::create(1),
            Username::create("marcus"),
            DecodedPassword::make("password123"),
            true
        );

        $clock = $this->createClock("UTC");
        $now = $clock->now();
        $decodedToken = $this->createDecodedToken(
            $user,
            $now,
            $now->modify("+1 hour")
        );
        $encodedToken = $this->createEncodedToken("potato");
        $tokenCache = $this->createEmptyTokenCache();
        $authenticationTokenEncoder = $this->createAuthenticationTokenEncoder();
        $authenticationService = $this->createAuthenticationService(
            $tokenCache,
            $this->createAuthenticationTokenDecoderWithReturn(
                $decodedToken
            ),
            $authenticationTokenEncoder,
            $this->createAuthenticationTokenValidator(
                $clock
            )
        );

        $authenticationService->validate(
            $encodedToken
        );
    }

    public function testIfAInformedTokenIsUnauthorizedBecauseOfDifferentTokenStringValues(): void
    {
        $this->expectException(UnauthorizedException::class);

        $user = $this->createUser(
            Id::create(1),
            Username::create("marcus"),
            DecodedPassword::make("password123"),
            true
        );

        $clock = $this->createClock("UTC");
        $now = $clock->now();
        $decodedToken = $this->createDecodedToken(
            $user,
            $now,
            $now->modify("+1 hour")
        );
        $encodedToken = $this->createEncodedToken("potato");
        $tokenCache = $this->createTokenCache(
            $encodedToken
        );
        $authenticationTokenEncoder = $this->createAuthenticationTokenEncoder();
        $authenticationService = $this->createAuthenticationService(
            $tokenCache,
            $this->createAuthenticationTokenDecoderWithReturn(
                $decodedToken
            ),
            $authenticationTokenEncoder,
            $this->createAuthenticationTokenValidator(
                $clock
            )
        );

        $newEncodedToken = $this->createEncodedToken("potato2");
        $authenticationService->validate(
            $newEncodedToken
        );
    }

    public function testIfAInformedTokenIsUnauthorizedBecauseOfDifferentUserIdValues(): void
    {
        $this->expectException(UnauthorizedException::class);

        $firstUser = $this->createUser(
            Id::create(1),
            Username::create("marcus"),
            DecodedPassword::make("password123"),
            true
        );

        $secondUser = $this->createUser(
            Id::create(2),
            Username::create("marcus"),
            DecodedPassword::make("password123"),
            true
        );

        $clock = $this->createClock("UTC");
        $now = $clock->now();
        $firstDecodedToken = $this->createDecodedToken(
            $firstUser,
            $now,
            $now->modify("+1 hour")
        );
        $secondDecodedToken = $this->createDecodedToken(
            $secondUser,
            $now,
            $now->modify("+1 hour")
        );
        $encodedToken = $this->createEncodedToken("potato");
        $tokenCache = $this->createTokenCache(
            $encodedToken
        );
        $authenticationTokenEncoder = $this->createAuthenticationTokenEncoder();
        $authenticationService = $this->createAuthenticationService(
            $tokenCache,
            $this->createAuthenticationTokenDecoderWithReturnOnConsecutiveCalls([
                $firstDecodedToken,
                $secondDecodedToken
            ]),
            $authenticationTokenEncoder,
            $this->createAuthenticationTokenValidator(
                $clock
            )
        );

        $authenticationService->validate(
            $encodedToken
        );
    }

    public function testIfAInformedTokenIsUnauthorizedBecauseOfDifferentUsernameValues(): void
    {
        $this->expectException(UnauthorizedException::class);

        $firstUser = $this->createUser(
            Id::create(1),
            Username::create("marcus"),
            DecodedPassword::make("password123"),
            true
        );

        $secondUser = $this->createUser(
            Id::create(1),
            Username::create("john"),
            DecodedPassword::make("password123"),
            true
        );

        $clock = $this->createClock("UTC");
        $now = $clock->now();
        $firstDecodedToken = $this->createDecodedToken(
            $firstUser,
            $now,
            $now->modify("+1 hour")
        );
        $secondDecodedToken = $this->createDecodedToken(
            $secondUser,
            $now,
            $now->modify("+1 hour")
        );
        $encodedToken = $this->createEncodedToken("potato");
        $tokenCache = $this->createTokenCache(
            $encodedToken
        );
        $authenticationTokenEncoder = $this->createAuthenticationTokenEncoder();
        $authenticationService = $this->createAuthenticationService(
            $tokenCache,
            $this->createAuthenticationTokenDecoderWithReturnOnConsecutiveCalls([
                $firstDecodedToken,
                $secondDecodedToken
            ]),
            $authenticationTokenEncoder,
            $this->createAuthenticationTokenValidator(
                $clock
            )
        );

        $authenticationService->validate(
            $encodedToken
        );
    }
}
