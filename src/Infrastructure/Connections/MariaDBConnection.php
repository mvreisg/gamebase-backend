<?php

namespace Mvreisg\GamebaseBackend\Infrastructure\Connections;

use PDO;

class MariaDBConnection
{
    public static function get(): PDO
    {
        return new PDO(
            $_SERVER['DATABASE_DSN'],
            $_SERVER['DATABASE_USERNAME'],
            $_SERVER['DATABASE_PASSWORD'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    }
}
