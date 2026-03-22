<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Utils;

use Psr\Http\Message\ResponseInterface;

class HttpMissingKeysInformer
{
    private static function inform(
        array $missingKeys,
        ResponseInterface $response,
        string $message,
        string $keysName
    ): ResponseInterface {
        if (count($missingKeys) > 0) {
            $response
                ->getBody()
                ->write(
                    json_encode([
                        "message" => $message,
                        $keysName => $missingKeys
                    ])
                );
        }
        return $response;
    }

    public static function informUriParams(
        array $missingKeys,
        ResponseInterface $response
    ): ResponseInterface {
        return self::inform($missingKeys, $response, "Missing URI params: ", "params");
    }

    public static function informQueryParams(
        array $missingKeys,
        ResponseInterface $response
    ): ResponseInterface {
        return self::inform($missingKeys, $response, "Missing query params: ", "params");
    }

    public static function informBodyKeys(
        array $missingKeys,
        ResponseInterface $response
    ): ResponseInterface {
        return self::inform($missingKeys, $response, "Missing body keys: ", "keys");
    }
}
