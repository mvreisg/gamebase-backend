<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Session\Login\Parameters;

use Mvreisg\GamebaseBackend\Domain\User\ValueObject\Password\Password;
use Mvreisg\GamebaseBackend\Domain\User\ValueObject\Username\Username;

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

    public function getUsername(): Username
    {
        return $this->username;
    }

    public function getPassword(): Password
    {
        return $this->password;
    }

    public function getOneWeekLogin(): bool
    {
        return $this->oneWeekLogin;
    }
}
