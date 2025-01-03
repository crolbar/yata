<?php

namespace App\Config;

use PDO;
use PDOException;
use App\Util\Env;

class Database
{
    private static $pdo;

    public static function getConnection(): PDO
    {
        if (self::$pdo === null) {
            Env::ParseEnv();

            $host       = getenv("HOST");
            $password   = getenv("PASSWORD");
            $user       = getenv("USER");
            $db         = getenv("DB");
            $uri        = "pgsql:host=$host;dbname=$db;";

            try {
                self::$pdo = new PDO($uri, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            } catch (PDOException $e) {
                // handle differently ?
                die("DB connection failed: " . $e->getMessage());
            }
        }

        return self::$pdo;
    }
}
