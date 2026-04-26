<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Views\Components;

class Nav
{
    public const NAV_DIRECTORY = "/pages/dashboard";

    public const ITEMS = [
        "Home" => self::NAV_DIRECTORY . "/home",
        "PDO Database" => self::NAV_DIRECTORY . "/database/pdo/pdo_database_view",
        "Phinx Startup" => self::NAV_DIRECTORY . "/database/phinx/phinx_startup_view",
        "Get PHP Defuse Encryption Key" => self::NAV_DIRECTORY . "/encryption/php_defuse/php_defuse_encryption_key_view",
        "Get Sodium Encryption Key" => self::NAV_DIRECTORY . "/encryption/sodium/sodium_encryption_key_view"
    ];

    public static function create(): self
    {
        return new self();
    }

    public function get(string $host): string
    {
        $nav = "<nav>";
        foreach (self::ITEMS as $title => $item) {
            $nav .= "<a class=\"m-1\" href=\"{$host}{$item}\">{$title}</a>";
        }
        $nav .= "</nav>";
        return $nav;
    }
}
