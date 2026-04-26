<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Views\Pages\Dashboard\Database\Pdo;

use Mvreisg\GamebaseBackend\Presentation\Http\Views\Components\Head;
use Mvreisg\GamebaseBackend\Presentation\Http\Views\Components\JavaScript;
use Mvreisg\GamebaseBackend\Presentation\Http\Views\Components\Nav;

class PdoDatabaseDashboardView
{
    private string $title;
    private string $database;
    private bool $exists;

    public function __construct(string $title, string $database, bool $exists)
    {
        $this->title = $title;
        $this->database = $database;
        $this->exists = $exists;
    }

    public static function create(
        string $title,
        string $database,
        bool $exists
    ): self {
        return new self($title, $database, $exists);
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
            "<div class=\"m-1\">" .
                "<span class=\"fw-semibold\">{$this->database}</span> status:" .
                $this->getExistsComponent($host, $this->exists) .
            "</div>" .
            JavaScript::create()->getBootstrapBundleScript($host) .
            JavaScript::create()->getSessionScript($host) .
            JavaScript::create()->getSessionValidationScript($host) .
        "</body>";
    }
}
