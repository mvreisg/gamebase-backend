<?php
    namespace Gamebase\Infrastructure\Database;

    use PDO;

    class MariaDBConnection {
        private const DSN = "mysql:host=localhost;dbname=gamebase";
        private const USERNAME = "root";
        private const PASSWORD = "";

        public static function get(): PDO
        {
            return new PDO(self::DSN, self::USERNAME, self::PASSWORD, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        }
    }
?>