<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Tests\Application\Services;

use Mvreisg\GamebaseBackend\Application\Services\Authentication\AuthenticationService;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Decoder\AuthenticationTokenDecoder;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Validate\AuthenticationTokenValidator;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Data\Encoded\EncodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Authorization\Exceptions\UnauthorizedException;
use Mvreisg\GamebaseBackend\Domain\Cache\Token\Interface\TokenCacheInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AuthenticationServiceTest extends TestCase
{
    private function createEncodedToken(
        string $token
    ): MockObject&\Mvreisg\GamebaseBackend\Domain\Authentication\Token\Data\Encoded\EncodedAuthenticationToken {
        $encodedToken = $this->createMock(EncodedAuthenticationToken::class);
        $encodedToken
            ->method("getToken")
            ->willReturn($token);
        return $encodedToken;
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

    private function createAuthenticationTokenDecoder(): MockObject&\Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Decoder\AuthenticationTokenDecoder
    {
        $authenticationTokenDecoder = $this->createMock(AuthenticationTokenDecoder::class);
        return $authenticationTokenDecoder;
    }

    private function createAuthenticationTokenValidator(): MockObject&\Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Validate\AuthenticationTokenValidator
    {
        $authenticationTokenValidator = $this->createMock(AuthenticationTokenValidator::class);
        return $authenticationTokenValidator;
    }

    private function createAuthenticationService(string $token): AuthenticationService
    {
        $tokenCache = $this->createTokenCache(
            $this->createEncodedToken($token)
        );
        $authenticationTokenDecoder = $this->createAuthenticationTokenDecoder();
        $authenticationTokenValidator = $this->createAuthenticationTokenValidator();

        return new AuthenticationService(
            $tokenCache,
            $authenticationTokenDecoder,
            $authenticationTokenValidator
        );
    }

    public function testIfTheServiceWillAuthorizeIfAExistantTokenIsInformed(): void
    {
        $token = "potato";
        $authenticationService = $this->createAuthenticationService(
            $token
        );

        $this->expectNotToPerformAssertions();

        $authenticationService->validate(
            new EncodedAuthenticationToken(
                $token
            )
        );
    }

    public function testIfTheServiceWillUnauthorizeIfAUnexistantTokenIsInformed(): void
    {
        $authenticationService = $this->createAuthenticationService(
            "potato"
        );

        $this->expectException(UnauthorizedException::class);

        $authenticationService->validate(
            new EncodedAuthenticationToken(
                "invalid"
            )
        );
    }
}
