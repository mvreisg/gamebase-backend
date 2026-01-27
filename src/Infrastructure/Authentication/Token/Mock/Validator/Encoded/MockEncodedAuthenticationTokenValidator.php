<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Mock\Validator\Encoded;

use Mvreisg\GamebaseBackend\Domain\Authentication\Token\State\Encoded\EncodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Authenticator\Token\Action\Validator\Decoded\EncodedAuthenticationTokenValidator;
use Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Mock\Decoder\MockAuthenticationTokenDecoder;
use Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Mock\Validator\Decoded\MockDecodedAuthenticationTokenValidator;

class MockEncodedAuthenticationTokenValidator implements EncodedAuthenticationTokenValidator
{
    private MockAuthenticationTokenDecoder $decoder;
    private MockDecodedAuthenticationTokenValidator $decodedValidator;

    public function __construct(
        MockAuthenticationTokenDecoder $decoder,
        MockDecodedAuthenticationTokenValidator $decodedValidator
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
