<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controller\Pages;

use Mvreisg\GamebaseBackend\Presentation\Http\Views\Pages\LoginView;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HttpLoginViewPageController
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $response
            ->getBody()
            ->write(
                LoginView::create(TITLE)
                    ->getHtml(
                        BASE_URL
                    )
            );
        return $response;
    }
}
