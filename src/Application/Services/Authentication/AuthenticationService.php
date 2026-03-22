<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services\Authentication;

use Mvreisg\GamebaseBackend\Domain\Authentication\Exceptions\InvalidTokenException;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\State\Encoded\EncodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Decoder\AuthenticationTokenDecoder;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Validator\Encoded\EncodedAuthenticationTokenValidator;
use Mvreisg\GamebaseBackend\Domain\Cache\Token\Interface\TokenCacheInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\UserRepositoryInterface;

class AuthenticationService
{
    private UserRepositoryInterface $userRepository;
    private TokenCacheInterface $tokenCache;
    private AuthenticationTokenDecoder $authenticationTokenDecoder;
    private EncodedAuthenticationTokenValidator $encodedAuthenticationTokenValidator;

    public function __construct(
        UserRepositoryInterface $userRepository,
        TokenCacheInterface $tokenCache,
        AuthenticationTokenDecoder $authenticationTokenDecoder,
        EncodedAuthenticationTokenValidator $encodedAuthenticationTokenValidator
    ) {
        $this->userRepository = $userRepository;
        $this->tokenCache = $tokenCache;
        $this->authenticationTokenDecoder = $authenticationTokenDecoder;
        $this->encodedAuthenticationTokenValidator = $encodedAuthenticationTokenValidator;
    }

    public function validate(EncodedAuthenticationToken $token): void
    {
        try {
            $decodedToken = $this->authenticationTokenDecoder->decode($token);

            $this->userRepository->checkIfExists(
                $decodedToken->getUserId()
            );

            $cachedToken = $this->tokenCache->get(
                $decodedToken->getUsername()
            );

            $isTheTokenTheSame = strcmp(
                $token->getToken(),
                $cachedToken->getToken()
            ) === 0;

            if ($isTheTokenTheSame === false) {
                throw new InvalidTokenException();
            }

            $this->encodedAuthenticationTokenValidator->validate($token);
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
