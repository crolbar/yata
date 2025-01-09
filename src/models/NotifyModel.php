<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class NotifyModel
{
    public static function update_fcm_device_token_for(string $sub, string $token): void
    {
        $pdo    = Database::getConnection();

        $query  = <<<SQL
        UPDATE users SET
        fcm_device_token = :token
        WHERE
        sub = :sub
        SQL;

        $stmt = $pdo->prepare($query);

        $stmt->bindValue(":token", $token, PDO::PARAM_STR);
        $stmt->bindValue(":sub", $sub, PDO::PARAM_STR);

        $stmt->execute();
    }

    public static function get_fcm_device_token_for(string $id): string|bool
    {
        $pdo    = Database::getConnection();

        $query  = <<<SQL
        SELECT fcm_device_token
        FROM users
        WHERE id = :id
        SQL;

        $stmt = $pdo->prepare($query);

        $stmt->bindValue(":id", $id, PDO::PARAM_STR);

        $stmt->execute();


        $res = $stmt->fetchAll();

        if (sizeof($res) !== 1) {
            return false;
        }

        if ($res[0]["fcm_device_token"] === null) {
            return false;
        }

        return $res[0]["fcm_device_token"];
    }
}
