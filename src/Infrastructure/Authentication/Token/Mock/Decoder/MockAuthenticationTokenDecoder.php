<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Mock\Decoder;

use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Decoder\AuthenticationTokenDecoder;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Decoder\Exceptions\AuthenticationTokenDecoderException;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\State\Decoded\DecodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\State\Encoded\EncodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Session\Data\SessionData;
use Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Mock\Clock\MockAuthenticationTokenClock;

class MockAuthenticationTokenDecoder implements AuthenticationTokenDecoder
{
    private MockAuthenticationTokenClock $clock;

    public function __construct(MockAuthenticationTokenClock $clock)
    {
        $this->clock = $clock;
    }

    public function decode(EncodedAuthenticationToken $token): DecodedAuthenticationToken
    {
        $decoded = base64_decode($token->getToken());
        $split = explode("|", $decoded);
        $count = count($split);
        if ($count < 3) {
            throw new AuthenticationTokenDecoderException(
                "Invalid number of parts."
            );
        }
        $seconds = intval($split[0]);
        $payload = json_decode($split[1]);
        $issuedAt = intval($split[2]);
        $expiresAt = $issuedAt + $seconds;
        if ($this->clock->now()->getTimestamp() >= $expiresAt) {
            throw new AuthenticationTokenDecoderException(
                "Expired date."
            );
        }
        if ($issuedAt > $this->clock->now()->getTimestamp()) {
            throw new AuthenticationTokenDecoderException(
                "Issued date is in the future."
            );
        }
        $data = SessionData::toObject($payload);
        return new DecodedAuthenticationToken(
            new \DateTimeImmutable("@$issuedAt")->setTimezone($this->clock->getTimezone()),
            new \DateTimeImmutable("@$expiresAt")->setTimezone($this->clock->getTimezone()),
            $data
        );
    }
}
