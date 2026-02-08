<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Jwt\Validator\Decoded;

use Mvreisg\GamebaseBackend\Domain\Authentication\Token\State\Decoded\DecodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Validator\Decoded\DecodedAuthenticationTokenValidator;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Validator\Decoded\Exceptions\DecodedAuthenticationTokenValidatorException;
use Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Jwt\Clock\JwtAuthenticationTokenClock;

class JwtDecodedAuthenticationTokenValidator implements DecodedAuthenticationTokenValidator
{
    private JwtAuthenticationTokenClock $clock;

    public function __construct(JwtAuthenticationTokenClock $clock)
    {
        $this->clock = $clock;
    }

    public function validate(DecodedAuthenticationToken $token): void
    {
        if ($token->getIssuedAt() > $this->clock->now()) {
            throw new DecodedAuthenticationTokenValidatorException("The token issue date is in the future.");
        }

        if ($token->getExpiresAt() < $this->clock->now()) {
            throw new DecodedAuthenticationTokenValidatorException("The token has expired.");
        }
    }
}
