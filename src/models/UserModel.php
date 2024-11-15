<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class UserModel
{
    public static function createUser(string $name, string $email, string $sub): void
    {
        $pdo    = Database::getConnection();

        $query  = "INSERT INTO users (name, email, sub) VALUES (:name, :email, :sub)";

        $stmt   = $pdo->prepare($query);

        $stmt->bindValue(":name", $name, PDO::PARAM_STR);
        $stmt->bindValue(":email", $email, PDO::PARAM_STR);
        $stmt->bindValue(":sub", $sub, PDO::PARAM_STR);

        $stmt->execute();

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
