<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class TaskModel
{
    public static function fetchAll(
        int $owner_id,
        int $week_start_unix,
        int $week_end_unix
    ): array|bool {
        $pdo    = Database::getConnection();

        $query  = <<<SQL
        SELECT
        id,
        title,
        EXTRACT(EPOCH FROM start_time) as start_time,
        EXTRACT(EPOCH FROM end_time) as end_time
        FROM tasks
        WHERE
        owner = :owner_id AND
        start_time
            BETWEEN
                to_timestamp(:week_start)::date - INTERVAL '1 day' AND
                to_timestamp(:week_end)::date + INTERVAL '1 day';
        SQL;
        $stmt   = $pdo->prepare($query);

        $stmt->bindValue(":owner_id", $owner_id, PDO::PARAM_INT);
        $stmt->bindValue(":week_start", $week_start_unix, PDO::PARAM_INT);
        $stmt->bindValue(":week_end", $week_end_unix, PDO::PARAM_INT);

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

    public static function createTask(
        string $title,
        int $start_time,
        int $end_time,
        int $owner_id,
    ): void {
        $pdo    = Database::getConnection();

        $query  = <<<SQL
        INSERT INTO tasks
            (
                title,
                start_time,
                end_time,
                owner
            )
        VALUES 
            (
                :title,
                to_timestamp(:start_time),
                to_timestamp(:end_time),
                :owner
            );
        SQL;

        $stmt   = $pdo->prepare($query);

        $stmt->bindValue(":title", $title, PDO::PARAM_STR);
        $stmt->bindValue(":start_time", $start_time, PDO::PARAM_INT);
        $stmt->bindValue(":end_time", $end_time, PDO::PARAM_INT);
        $stmt->bindValue(":owner", $owner_id, PDO::PARAM_INT);

        $stmt->execute();
    }

    public static function updateTask(
        int $id,
        string $title,
        int $start_time,
        int $end_time,
        int $owner_id,
    ): void {
        $pdo    = Database::getConnection();

        $query  = <<<SQL
        UPDATE tasks SET
        title       = :title,
        start_time  = to_timestamp(:start_time),
        end_time    = to_timestamp(:end_time),
        updated_at  = CURRENT_TIMESTAMP
        WHERE
        id      = :id AND
        owner   = :owner_id;
        SQL;

        $stmt   = $pdo->prepare($query);

        $stmt->bindValue(":title", $title, PDO::PARAM_STR);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->bindValue(":start_time", $start_time, PDO::PARAM_INT);
        $stmt->bindValue(":end_time", $end_time, PDO::PARAM_INT);
        $stmt->bindValue(":owner_id", $owner_id, PDO::PARAM_INT);

        $stmt->execute();
    }
}
