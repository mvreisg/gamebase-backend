<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Authentication\Service;

use Mvreisg\GamebaseBackend\Application\Authentication\Data\AuthenticationData;
use Mvreisg\GamebaseBackend\Application\Authentication\Exception\InvalidTokenException;
use Mvreisg\GamebaseBackend\Application\Authentication\Exception\UnexistantTokenException;
use Mvreisg\GamebaseBackend\Application\Authentication\Token\AuthenticationToken;
use Mvreisg\GamebaseBackend\Application\Authentication\Token\Cache\AuthenticationTokenCacheInterface;
use Mvreisg\GamebaseBackend\Application\Authentication\Token\Provider\AuthenticationTokenProvider;
use Psr\Log\LoggerInterface;

class AuthenticationService
{
    private AuthenticationTokenCacheInterface $tokenCache;
    private AuthenticationTokenProvider $tokenProvider;
    private LoggerInterface $logger;

    public function __construct(
        AuthenticationTokenCacheInterface $tokenCache,
        AuthenticationTokenProvider $tokenProvider,
        LoggerInterface $logger
    ) {
        $this->tokenCache = $tokenCache;
        $this->tokenProvider = $tokenProvider;
        $this->logger = $logger;
    }

    public function encode(
        AuthenticationData $authenticationData,
        \DateInterval $interval
    ): string {
        try {
            return $this->tokenProvider->encode(
                $authenticationData,
                $interval
            );
        } catch (\Throwable $e) {
            $this->logger->error(
                "Error encoding token",
                [
                    "error" => $e->getMessage()
                ]
            );
            throw $e;
        }
    }

    public function decode(string $token): AuthenticationToken
    {
        try {
            return $this->tokenProvider->decode($token);
        } catch (\Throwable $e) {
            $this->logger->error(
                "Error decoding token",
                [
                    "error" => $e->getMessage()
                ]
            );
            throw $e;
        }
    }

    public function validate(string $informedToken): AuthenticationToken
    {
        try {
            $decodedToken = $this->decode($informedToken);

            $this->tokenProvider->validate($decodedToken);

            $id = $decodedToken->getAuthenticationData()->getUserId();
            $username = $decodedToken->getAuthenticationData()->getUsername();

            $exists = $this->tokenCache->exists(
                $username->getValue()
            );

            if ($exists === false) {
                throw new UnexistantTokenException();
            }

            $cachedToken = $this->tokenCache->get(
                $username->getValue()
            );

            $isTokensIdenticals = strcmp(
                $informedToken,
                $cachedToken
            ) === 0;

            if ($isTokensIdenticals === false) {
                throw new InvalidTokenException();
            }

            $cachedResult = $this->decode($cachedToken);

            $isIdIdenticals = $id->getValue() === $cachedResult->getAuthenticationData()->getUserId()->getValue();

            if ($isIdIdenticals === false) {
                throw new InvalidTokenException();
            }

            $isUsernamesIdenticals = strcmp(
                $username->getValue(),
                $cachedResult->getAuthenticationData()->getUsername()->getValue()
            ) === 0;

            if ($isUsernamesIdenticals === false) {
                throw new InvalidTokenException();
            }

            return $decodedToken;
        } catch (\Throwable $e) {
            $this->logger->error(
                "Error validating token",
                [
                    "error" => $e->getMessage()
                ]
            );
            throw $e;
        }
    }
}
