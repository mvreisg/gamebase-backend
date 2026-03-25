<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services\Session\Login\Parameters;

use Mvreisg\GamebaseBackend\Domain\Entities\Password;
use Mvreisg\GamebaseBackend\Domain\Entities\Username;

class SessionLoginParameters
{
    private Username $username;
    private Password $password;
    private bool $oneWeekLogin;

    public function __construct(Username $username, Password $password, bool $oneWeekLogin)
    {
        $this->username = $username;
        $this->password = $password;
        $this->oneWeekLogin = $oneWeekLogin;
    }

    public function getUsernameValue(): string
    {
        return $this->username->getValue();
    }

    public function getPasswordValue(): string
    {
        return $this->password->getValue();
    }

    public function getOneWeekLogin(): bool
    {
        return $this->oneWeekLogin;
    }
}
