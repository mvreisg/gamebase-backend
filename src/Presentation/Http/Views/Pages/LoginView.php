<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Views\Pages;

use Mvreisg\GamebaseBackend\Presentation\Http\Views\Components\Head;
use Mvreisg\GamebaseBackend\Presentation\Http\Views\Components\JavaScript;
use Mvreisg\GamebaseBackend\Presentation\Http\Views\Components\Login\LoginForm;

class LoginView
{
    public static function create(): self
    {
        return new self();
    }

    public function getHtml(string $host, string $title): string
    {
        return "
        <!DOCTYPE html>
        <html lang=\"en\">
            {$this->getHead($host, $title)}
            {$this->getBody($host, $title)}
        </html>";
    }

    public function getHead(string $host, string $title): string
    {
        return Head::create()->get($host, $title);
    }

    public function getBody(string $host, string $title): string
    {
        return "
        <body>
            <h1 class=\"m-1\">{$title}</h1>" .
            JavaScript::create()->getBootstrapBundleScript($host) .
            JavaScript::create()->getSessionScript($host) .
            JavaScript::create()->getSessionValidationScript($host) .
            LoginForm::create()->getHtml() .
            LoginForm::create()->getScript($host) .
        "</body>";
    }
}
