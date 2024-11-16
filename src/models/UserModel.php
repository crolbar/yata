<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class UserModel
{
    public static function createUser(string $name, string $email, string $sub, string $refresh_token): void
    {
        $pdo    = Database::getConnection();

        $query  = <<<SQL
        INSERT INTO users
        (name, email, sub, refresh_token)
        VALUES
        (:name, :email, :sub, :refresh_token)
        SQL;

        $stmt   = $pdo->prepare($query);

        $stmt->bindValue(":name", $name, PDO::PARAM_STR);
        $stmt->bindValue(":email", $email, PDO::PARAM_STR);
        $stmt->bindValue(":sub", $sub, PDO::PARAM_STR);
        $stmt->bindValue(":refresh_token", $refresh_token, PDO::PARAM_STR);

        $stmt->execute();
    }

    public static function getRefreshToken(string $sub): string|bool
    {
        $pdo    = Database::getConnection();

        $query  = <<<SQL
        SELECT refresh_token FROM users WHERE sub = :sub
        SQL;

        $stmt   = $pdo->prepare($query);

        $stmt->bindValue(":sub", $sub);

        $stmt->execute();

        $res = $stmt->fetchAll();

        if (sizeof($res) !== 1) {
            return false;
        }

        return $res[0]["refresh_token"];
    }

    /*
     * @return int|true returns the user id if exists, true is not
     */
    public static function isNewUser(string $sub): int|bool
    {
        $pdo    = Database::getConnection();

        $query  = "SELECT id FROM users WHERE sub = :sub";

        $stmt = $pdo->prepare($query);
        $stmt->bindValue(":sub", $sub, PDO::PARAM_STR);
        $stmt->execute();

        $res = $stmt->fetchAll();

        if (sizeof($res) === 0) {
            return true;
        }

        return $res[0]["id"];
    }
}
