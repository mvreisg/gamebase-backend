<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Middlewares\Controllers;

use Mvreisg\GamebaseBackend\Presentation\Http\Exceptions\HttpUnauthorizedException;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpRequest;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpResponse;
use Mvreisg\GamebaseBackend\Presentation\Http\Exceptions\HttpBadRequestException;
use Mvreisg\GamebaseBackend\Presentation\Http\Exceptions\HttpForbiddenException;
use Mvreisg\GamebaseBackend\Presentation\Http\Exceptions\HttpInternalServerError;
use Mvreisg\GamebaseBackend\Presentation\Http\Exceptions\HttpNotFoundException;

class HttpControllerHandler
{
    public static function use(
        HttpRequest $request,
        HttpResponse $response,
        callable $callback
    ) {
        try {
            $callback($request, $response);
        } catch (
            HttpBadRequestException
            $e
        ) {
            $response
                ->setBody([
                    "message" => $e->getMessage()
                ])
                ->setStatusBadRequest()
                ->sendJson();
        } catch (
            HttpUnauthorizedException
            $e
        ) {
            $response
                ->setBody([
                    "message" => $e->getMessage()
                ])
                ->setStatusUnauthorized()
                ->sendJson();
        } catch (
            HttpForbiddenException
            $e
        ) {
            $response
                ->setBody([
                    "message" => $e->getMessage()
                ])
                ->setStatusForbidden()
                ->sendJson();
        } catch (
            HttpNotFoundException
            $e
        ) {
            $response
                ->setBody([
                    "message" => $e->getMessage()
                ])
                ->setStatusNotFound()
                ->sendJson();
        } catch (
            HttpInternalServerError |
            \Throwable
            $e
        ) {
            $response
                ->setBody([
                    "message" => $e->getMessage()
                ])
                ->setStatusInternalServerError()
                ->sendJson();
        }
    }
}
