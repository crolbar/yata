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
        fcm_device_token = :token,
        updated_at       = CURRENT_TIMESTAMP
        WHERE
        sub = :sub
        SQL;

        $stmt = $pdo->prepare($query);

        $stmt->bindValue(":token", $token, PDO::PARAM_STR);
        $stmt->bindValue(":sub", $sub, PDO::PARAM_STR);

        $stmt->execute();
    }

    // return false on nothing found for id, device token is not set
    // and if wants_notifications is false
    public static function get_fcm_device_token_for(string $id): string|bool
    {
        $pdo    = Database::getConnection();

        $query  = <<<SQL
        SELECT
            fcm_device_token,
            wants_notifications
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

        if ($res[0]["wants_notifications"] === false) {
            return false;
        }

        return $res[0]["fcm_device_token"];
    }

    public static function update_notifications_for(string $sub, bool $wants_notifications): void
    {
        $pdo    = Database::getConnection();

        $query  = <<<SQL
        UPDATE users SET
        wants_notifications = :wants_notifications,
        updated_at          = CURRENT_TIMESTAMP
        WHERE
        sub = :sub
        SQL;

        $stmt = $pdo->prepare($query);

        $stmt->bindValue(":sub", $sub, PDO::PARAM_STR);
        $stmt->bindValue(":wants_notifications", $wants_notifications, PDO::PARAM_BOOL);

        $stmt->execute();
    }

    public static function get_status_of_notifications_for(string $sub): string|bool
    {
        $pdo    = Database::getConnection();

        $query  = <<<SQL
        SELECT wants_notifications
        FROM users
        WHERE sub = :sub
        SQL;

        $stmt = $pdo->prepare($query);

        $stmt->bindValue(":sub", $sub, PDO::PARAM_STR);

        $stmt->execute();

        $res = $stmt->fetchAll();

        if (sizeof($res) !== 1) {
            return false;
        }

        return $res[0]["wants_notifications"] ? "true" : "false";
    }
}
