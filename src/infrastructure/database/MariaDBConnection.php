<?php
    namespace Mvreisg\GamebaseBackend\Infrastructure\Database;

    use PDO;

    class MariaDBConnection {
        public static function get(): PDO
        {
            /**
             * @var PDO
             */
            $pdo = $_SERVER["ENVIRONMENT"] === "development" ?
                new PDO(
                    $_SERVER["MARIADB_DATABASE_DEVELOPMENT_DSN"], 
                    $_SERVER["MARIADB_DATABASE_DEVELOPMENT_USERNAME"], 
                    $_SERVER["MARIADB_DATABASE_DEVELOPMENT_PASSWORD"], 
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    ]
                ) : 
                new PDO(
                    $_SERVER["MARIADB_DATABASE_PRODUCTION_DSN"], 
                    $_SERVER["MARIADB_DATABASE_PRODUCTION_USERNAME"], 
                    $_SERVER["MARIADB_DATABASE_PRODUCTION_PASSWORD"], 
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    ]
                );
            return $pdo;
        }
    }
?>