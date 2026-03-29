<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services\Authentication;

use Mvreisg\GamebaseBackend\Domain\Authentication\Data\AuthenticationData;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Data\Encoded\EncodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Decoder\AuthenticationTokenDecoder;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Encoder\AuthenticationTokenEncoder;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Validate\AuthenticationTokenValidator;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Data\Decoded\DecodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Authorization\Exceptions\UnauthorizedException;
use Mvreisg\GamebaseBackend\Domain\Cache\Token\Interface\TokenCacheInterface;

class AuthenticationService
{
    private TokenCacheInterface $tokenCache;
    private AuthenticationTokenDecoder $authenticationTokenDecoder;
    private AuthenticationTokenEncoder $authenticationTokenEncoder;
    private AuthenticationTokenValidator $authenticationTokenValidator;

    public function __construct(
        TokenCacheInterface $tokenCache,
        AuthenticationTokenDecoder $authenticationTokenDecoder,
        AuthenticationTokenEncoder $authenticationTokenEncoder,
        AuthenticationTokenValidator $authenticationTokenValidator
    ) {
        $this->tokenCache = $tokenCache;
        $this->authenticationTokenDecoder = $authenticationTokenDecoder;
        $this->authenticationTokenEncoder = $authenticationTokenEncoder;
        $this->authenticationTokenValidator = $authenticationTokenValidator;
    }

    public function encode(
        AuthenticationData $authenticationData,
        \DateInterval $interval
    ): EncodedAuthenticationToken {
        try {
            return $this->authenticationTokenEncoder->encode(
                $authenticationData,
                $interval
            );
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function decode(
        EncodedAuthenticationToken $token
    ): DecodedAuthenticationToken {
        try {
            return $this->authenticationTokenDecoder->decode($token);
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function validate(EncodedAuthenticationToken $informedToken): DecodedAuthenticationToken
    {
        try {
            $decodedToken = $this->decode($informedToken);

            $this->authenticationTokenValidator->validate($decodedToken);

            $id = $decodedToken->getUserId();
            $username = $decodedToken->getUsername();

            $exists = $this->tokenCache->exists(
                $username
            );

            if ($exists === false) {
                throw new UnauthorizedException();
            }

            $cachedToken = $this->tokenCache->get(
                $username
            );

            $isTokensIdenticals = strcmp(
                $informedToken->getToken(),
                $cachedToken->getToken()
            ) === 0;

            if ($isTokensIdenticals === false) {
                throw new UnauthorizedException();
            }

            $cachedResult = $this->decode($cachedToken);

            $isIdIdenticals = $id->getValue() === $cachedResult->getUserId()->getValue();

            if ($isIdIdenticals === false) {
                throw new UnauthorizedException();
            }

            $isUsernamesIdenticals = strcmp(
                $username->getValue(),
                $cachedResult->getUsername()->getValue()
            ) === 0;

            if ($isUsernamesIdenticals === false) {
                throw new UnauthorizedException();
            }

            return $decodedToken;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
