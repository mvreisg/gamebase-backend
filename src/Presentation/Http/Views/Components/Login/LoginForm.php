<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Views\Components\Login;

class LoginForm
{
    public static function create(): self
    {
        return new self();
    }

    public function getHtml(): string
    {
        return "
            <form id=\"login-form\">
                <label class=\"m-1\">Username:</label>
                <input id=\"username\" type=\"text\" name=\"username\" class=\"m-1\" />
                <label class=\"m-1\">Password:</label>
                <input id=\"password\" type=\"password\" name=\"password\" class=\"m-1\" />
                <button class=\"m-1\">Login</button>
            </form>
        ";
    }

    public function getScript(string $host): string
    {
        return "
            <script type=\"module\">
                import session from '$host/javascript/session.js';

                const form = document.getElementById(\"login-form\");
                form.addEventListener(\"submit\", async (e) => {
                    e.preventDefault();
                    const username = document.getElementById(\"username\").value;
                    const password = document.getElementById(\"password\").value;

                    const result = await session.login({
                        username,
                        password
                    }, \"$host\");
                    
                    if (result.status === \"success\") {
                        window.location.href = '$host/pages/dashboard/home';
                    } else {
                        alert(\"Login failed\");
                    }
                });
            </script>
        ";
    }
}
