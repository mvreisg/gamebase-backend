<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Mock\Validator\Decoded;

use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Validator\Decoded\DecodedAuthenticationTokenValidator;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\State\Decoded\DecodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Mock\Clock\MockAuthenticationTokenClock;

class MockDecodedAuthenticationTokenValidator implements DecodedAuthenticationTokenValidator
{
    private MockAuthenticationTokenClock $clock;

    public function __construct(MockAuthenticationTokenClock $clock)
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
