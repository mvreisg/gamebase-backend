<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Mock\Validator\Decoded;

use Mvreisg\GamebaseBackend\Domain\Authentication\Token\State\Decoded\DecodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Authenticator\Token\Action\Validator\Decoded\DecodedAuthenticationTokenValidator;
use Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Jwt\Clock\JwtAuthenticationTokenClock;

class MockDecodedAuthenticationTokenValidator implements DecodedAuthenticationTokenValidator
{
    private JwtAuthenticationTokenClock $clock;

    public function __construct(JwtAuthenticationTokenClock $clock)
    {
        $this->clock = $clock;
    }

    public function validate(DecodedAuthenticationToken $token): void
    {
        if ($this->clock->now()->getTimestamp() >= $token->getExpiresAt()) {
            throw new \DomainException(
                "Token expired."
            );
        }

        if ($token->getIssuedAt() > $this->clock->now()->getTimestamp()) {
            throw new \DomainException(
                "Token with invalid issue date."
            );
        }
    }
}
