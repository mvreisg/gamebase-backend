<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controllers\Factories;

use Mvreisg\GamebaseBackend\Presentation\Http\Controllers\HttpAuthenticationController;
use Mvreisg\GamebaseBackend\Presentation\Http\Services\Factories\Authentication\HttpAuthenticationServiceFactory;

class HttpAuthenticationControllerFactory
{
    public static function make(): HttpAuthenticationController
    {
        try {
            $service = HttpAuthenticationServiceFactory::make();

            $controller = new HttpAuthenticationController(
                $service
            );

            return $controller;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
