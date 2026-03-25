<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Jwt\Validator\Encoded;

use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Data\Encoded\EncodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Validator\Encoded\EncodedAuthenticationTokenValidator;
use Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Jwt\Validator\Decoded\JwtDecodedAuthenticationTokenValidator;
use Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Jwt\Decoder\JwtAuthenticationTokenDecoder;

class JwtEncodedAuthenticationTokenValidator implements EncodedAuthenticationTokenValidator
{
    private JwtAuthenticationTokenDecoder $decoder;
    private JwtDecodedAuthenticationTokenValidator $decodedValidator;

    public function __construct(
        JwtAuthenticationTokenDecoder $decoder,
        JwtDecodedAuthenticationTokenValidator $decodedValidator
    ) {
        $this->decoder = $decoder;
        $this->decodedValidator = $decodedValidator;
    }

    public function validate(EncodedAuthenticationToken $token): void
    {
        $decodedToken = $this->decoder->decode($token);

        $this->decodedValidator->validate($decodedToken);
    }
}
