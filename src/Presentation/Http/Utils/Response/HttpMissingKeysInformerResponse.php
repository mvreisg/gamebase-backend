<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Utils\Response;

use Mvreisg\GamebaseBackend\Domain\Utils\Arrays\ArrayMissingKeysInformer;
use Psr\Http\Message\ResponseInterface;

class HttpMissingKeysInformerResponse
{
    private static function answer(ResponseInterface $response, array $body): ResponseInterface
    {
        $response
            ->getBody()
            ->write(
                json_encode(
                    $body
                )
            );
        return $response->withStatus(404);
    }

    public static function getStatusAsArrayOfUriParams(
        ResponseInterface $response,
        array $missingKeys
    ): ResponseInterface {
        $body = ArrayMissingKeysInformer::getStatusAsArray($missingKeys, "Missing URI params: ", "params");
        return self::answer($response, $body);
    }

    public static function getStatusAsArrayOfQueryParams(
        ResponseInterface $response,
        array $missingKeys
    ): ResponseInterface {
        $body = ArrayMissingKeysInformer::getStatusAsArray($missingKeys, "Missing query params: ", "params");
        return self::answer($response, $body);
    }

    public static function getStatusAsArrayOfBodyKeys(
        ResponseInterface $response,
        array $missingKeys
    ): ResponseInterface {
        $body = ArrayMissingKeysInformer::getStatusAsArray($missingKeys, "Missing body keys: ", "keys");
        return self::answer($response, $body);
    }
}
