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

            $host       = getenv("DB_HOST");
            $password   = getenv("DB_PASSWORD");
            $user       = getenv("DB_USER");
            $db         = getenv("DB_DB");
            $uri        = "pgsql:host=$host;dbname=$db;sslmode=require";

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
