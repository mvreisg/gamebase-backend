<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Views\Pages\Dashboard;

use Mvreisg\GamebaseBackend\Presentation\Http\Views\Components\Head;
use Mvreisg\GamebaseBackend\Presentation\Http\Views\Components\JavaScript;
use Mvreisg\GamebaseBackend\Presentation\Http\Views\Components\Nav;

class DashboardHomeView
{
    private string $title;

    public function __construct(string $title)
    {
        $this->title = $title;
    }

    public static function create(string $title): self
    {
        return new self($title);
    }

    public function getHtml(string $host): string
    {
        return "
        <!DOCTYPE html>
        <html lang=\"en\">
            {$this->getHead()}
            {$this->getBody($host)}            
        </html>";
    }

    public function getHead(): string
    {
        return Head::create()->get($this->title);
    }

    public function getBody(string $host): string
    {
        return "
        <body>
            <h1 class=\"m-1\">{$this->title}</h1>" .
            Nav::create()->get() .
            JavaScript::create()->getBootstrapBundleScript($host) .
            JavaScript::create()->getSessionScript($host) .
            JavaScript::create()->getSessionValidationScript($host) .
        "</body>";
    }
}
