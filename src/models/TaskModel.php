<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class TaskModel
{
    public static function fetchAll(int $owner_id): array|bool
    {
        $pdo    = Database::getConnection();

        $query  = <<<SQL
        SELECT
        id,
        title,
        EXTRACT(EPOCH FROM start_time) as start_time,
        EXTRACT(EPOCH FROM end_time) as end_time
        FROM tasks
        WHERE owner = :owner_id;
        SQL;
        $stmt   = $pdo->prepare($query);

        $stmt->bindValue(":owner_id", $owner_id, PDO::PARAM_INT);
        $stmt->execute();

        $res = $stmt->fetchAll();

        return $res;
    }

    public static function deleteById(int $id, int $owner_id): void
    {
        $pdo    = Database::getConnection();
        $query  = <<<SQL
        DELETE FROM tasks
        WHERE
        id = :id AND
        owner = :owner_id;
        SQL;

        $stmt   = $pdo->prepare($query);

        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->bindValue(":owner_id", $owner_id, PDO::PARAM_INT);

        $stmt->execute();
    }

    public static function createTask(string $title, int $owner_id): void
    {
        $pdo    = Database::getConnection();

        $query  = <<<SQL
        INSERT INTO tasks
            (title, owner)
        VALUES 
            (:title, :owner);
        SQL;

        $stmt   = $pdo->prepare($query);

        $stmt->bindValue(":title", $title, PDO::PARAM_STR);
        $stmt->bindValue(":owner", $owner_id, PDO::PARAM_INT);

        $stmt->execute();
    }

    public static function updateTask(int $id, string $title, int $owner_id): void
    {
        $pdo    = Database::getConnection();

        $query  = <<<SQL
        UPDATE tasks SET
        title = :title,
        updated_at = CURRENT_TIMESTAMP
        WHERE
        id = :id AND
        owner = :owner_id;
        SQL;

        $stmt   = $pdo->prepare($query);

        $stmt->bindValue(":title", $title, PDO::PARAM_STR);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->bindValue(":owner_id", $owner_id, PDO::PARAM_INT);

        $stmt->execute();
    }
}
