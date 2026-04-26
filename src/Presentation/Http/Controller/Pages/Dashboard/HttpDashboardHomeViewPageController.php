<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controller\Pages\Dashboard;

use Mvreisg\GamebaseBackend\Presentation\Http\Views\Pages\Dashboard\DashboardHomeView;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HttpDashboardHomeViewPageController
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $response
            ->getBody()
            ->write(
                DashboardHomeView::create(TITLE)
                    ->getHtml(
                        BASE_URL
                    )
            );
        return $response;
    }
}
