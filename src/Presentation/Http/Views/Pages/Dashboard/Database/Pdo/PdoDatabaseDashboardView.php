<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Views\Pages\Dashboard\Database\Pdo;

use Mvreisg\GamebaseBackend\Presentation\Http\Views\Components\Head;
use Mvreisg\GamebaseBackend\Presentation\Http\Views\Components\JavaScript;
use Mvreisg\GamebaseBackend\Presentation\Http\Views\Components\Nav;

class PdoDatabaseDashboardView
{
    public static function create(): self
    {
        return new self();
    }

    public function getExistsComponent(string $host, bool $exists): string
    {
        if ($exists) {
            return "
                <span class=\"fw-semibold\" style=\"color: lime\">exists.</span>
                <a style=\"color: red\" class=\"fw-semibold mt-1 ms-1\" href=\"$host" . Nav::ITEMS["PDO Database"] . "?action=drop\">Drop</a>
            ";
        } else {
            return "
                <span class=\"fw-semibold\" style=\"color: red\">unexistant.</span>
                <a style=\"color: lime\" class=\"fw-semibold mt-1 ms-1\" href=\"$host" . Nav::ITEMS["PDO Database"] . "?action=create\">Create</a>
            ";
        }
    }

    public function getHtml(string $host, string $title, string $database, bool $exists): string
    {
        return "
        <!DOCTYPE html>
        <html lang=\"en\">
            {$this->getHead($host, $title)}
            {$this->getBody($host, $title, $database, $exists)}
        </html>";
    }

    public function getHead(string $host, string $title): string
    {
        return Head::create()->get($host, $title);
    }

    public function getBody(string $host, string $title, string $database, bool $exists): string
    {
        return "
        <body>
            <h1 class=\"m-1\">{$title}</h1>" .
            Nav::create()->get($host) .
            "<div class=\"m-1\">" .
                "<span class=\"fw-semibold\">{$database}</span> status:" .
                $this->getExistsComponent($host, $exists) .
            "</div>" .
            JavaScript::create()->getBootstrapBundleScript($host) .
            JavaScript::create()->getSessionScript($host) .
            JavaScript::create()->getSessionValidationScript($host) .
        "</body>";
    }
}
