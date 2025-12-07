<?php
namespace App\Framework\Database;

use \PDO;

class Connection {
    private static ?PDO $instance = null;

    public static function getInstance(
        string $host,
        string $dbname,
        string $user,
        string $password,
        int $port = 5432
    ): PDO {
        if (self::$instance === null) {
            $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";
            self::$instance = new PDO($dsn, $user, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        }

        return self::$instance;
    }
}

