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
        $pdo = Database::getConnection();

        $query = "delete from tasks where id = $id";
        $pdo->exec($query);
    }

    public static function createTask(string $title): void
    {
        $pdo = Database::getConnection();

        $query = "insert into tasks (title) values ('$title')";
        $pdo->exec($query);
    }
}
