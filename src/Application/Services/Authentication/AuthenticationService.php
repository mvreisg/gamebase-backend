<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services\Authentication;

use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Data\Encoded\EncodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Decoder\AuthenticationTokenDecoder;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Validate\AuthenticationTokenValidator;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Data\Decoded\DecodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Authorization\Exceptions\UnauthorizedException;
use Mvreisg\GamebaseBackend\Domain\Cache\Token\Interface\TokenCacheInterface;

class AuthenticationService
{
    private TokenCacheInterface $tokenCache;
    private AuthenticationTokenDecoder $authenticationTokenDecoder;
    private AuthenticationTokenValidator $authenticationTokenValidator;

    public function __construct(
        TokenCacheInterface $tokenCache,
        AuthenticationTokenDecoder $authenticationTokenDecoder,
        AuthenticationTokenValidator $authenticationTokenValidator
    ) {
        $this->tokenCache = $tokenCache;
        $this->authenticationTokenDecoder = $authenticationTokenDecoder;
        $this->authenticationTokenValidator = $authenticationTokenValidator;
    }

    public function validate(EncodedAuthenticationToken $token): DecodedAuthenticationToken
    {
        try {
            $decodedToken = $this->authenticationTokenDecoder->decode($token);

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
                $token->getToken(),
                $cachedToken->getToken()
            ) === 0;

            if ($isTokensIdenticals === false) {
                throw new UnauthorizedException();
            }

            $cachedResult = $this->authenticationTokenDecoder->decode($token);

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
