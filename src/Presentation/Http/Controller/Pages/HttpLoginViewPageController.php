<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controller\Pages;

use Mvreisg\GamebaseBackend\Presentation\Http\Option\HttpOptions;
use Mvreisg\GamebaseBackend\Presentation\Http\Views\Pages\LoginView;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HttpLoginViewPageController
{
    private HttpOptions $options;

    public function __construct(HttpOptions $options)
    {
        $this->options = $options;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $response
            ->getBody()
            ->write(
                LoginView::create()->getHtml(
                    $this->options->getHost(),
                    $this->options->getTitle()
                )
            );
        return $response;
    }
}
