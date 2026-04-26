<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Views\Components;

class Head
{
    public static function create(): self
    {
        return new self();
    }

    public function get(string $host, string $title): string
    {
        return "<head>"
            . $this->getMetaCharset()
            . $this->getMetaViewport()
            . $this->getTitle($title)
            . $this->getBootstrapCss($host)
            . $this->getCustomStyles()
        . "</head>";
    }

    public function getMetaCharset(): string
    {
        return "<meta charset=\"UTF-8\">";
    }

    public function getMetaViewport(): string
    {
        return "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">";
    }

    public function getTitle(string $title): string
    {
        return "<title>{$title}</title>";
    }

    public function getBootstrapCss(string $host): string
    {
        return "<link rel=\"stylesheet\" href=\"$host/css/bootstrap.min.css\">";
    }

    public function getCustomStyles(): string
    {
        return "
            <style>
                body {
                    background-color: rgb(75, 75, 75); 
                    color: white;
                }

                a {
                    color: white;
                }
            </style>";
    }
}
