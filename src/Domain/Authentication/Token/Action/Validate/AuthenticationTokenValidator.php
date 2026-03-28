<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Validate;

use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Validate\Exceptions\AuthenticationTokenValidatorException;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Data\Decoded\DecodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Interfaces\ClockInterface;

class AuthenticationTokenValidator
{
    private ClockInterface $clock;

    public function __construct(ClockInterface $clock)
    {
        $this->clock = $clock;
    }

    public function validate(DecodedAuthenticationToken $token): void
    {
        if ($token->getIssuedAt()->getTimestamp() > $this->clock->now()->getTimestamp()) {
            throw new AuthenticationTokenValidatorException(
                "The token issue date is in the future."
            );
        }

        if ($this->clock->now()->getTimestamp() > $token->getExpiresAt()->getTimestamp()) {
            throw new AuthenticationTokenValidatorException(
                "The token has expired."
            );
        }
    }
}
