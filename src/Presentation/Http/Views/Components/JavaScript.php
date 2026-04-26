<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Views\Components;

class JavaScript
{
    public static function create(): self
    {
        return new self();
    }

    public function getBootstrapBundleScript(string $host): string
    {
        return "<script src=\"$host/javascript/bootstrap.bundle.min.js\"></script>";
    }

    public function getClipboardScript(string $host): string
    {
        return "<script src=\"$host/javascript/clipboard.js\"></script>";
    }

    public function getSessionScript(string $host): string
    {
        return "<script type=\"module\" src=\"$host/javascript/session.js\"></script>";
    }

    public function getSessionValidationScript(string $host): string
    {
        return "
            <script type=\"module\">     
                import session from '$host/javascript/session.js';       
                const isValid = await session.validate(\"$host\")
                if (isValid === false && window.location.pathname !== '/pages/login') {
                    alert(\"An error occurred while validating the session. Please try again.\");
                    window.location.href = \"$host/pages/login\";
                } 
                if (isValid && window.location.pathname === '/pages/login') {
                    window.location.href = \"$host/pages/dashboard/home\";
                }
            </script>
        ";
    }
}
