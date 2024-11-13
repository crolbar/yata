<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class TaskModel
{
    public static function fetchAll(): array|bool
    {
        $pdo = Database::getConnection();

        $query = "select id, title from tasks";
        $res = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);

        return $res;
    }

    public static function deleteById(int $id): void
    {
        $pdo    = Database::getConnection();
        $query  = "delete from tasks where id = :id";
        $stmt   = $pdo->prepare($query);

        $stmt->bindValue(":id", $id, PDO::PARAM_INT);

        $stmt->execute();
    }

    public static function createTask(string $title): void
    {
        $pdo    = Database::getConnection();
        $query  = "insert into tasks (title) values (:title)";
        $stmt   = $pdo->prepare($query);

        $stmt->bindValue(":title", $title, PDO::PARAM_STR);

        $stmt->execute();
    }

    public static function updateTask(string $id, string $title): void
    {
        $pdo    = Database::getConnection();
        $query  = "update tasks set title = :title where id = :id";
        $stmt   = $pdo->prepare($query);

        $stmt->bindValue(":title",  $title, PDO::PARAM_STR);
        $stmt->bindValue(":id",     $id,    PDO::PARAM_INT);

        $stmt->execute();
    }
}
