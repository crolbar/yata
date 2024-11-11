<?php

namespace App\Controllers;

use App\Routing\Router;
use App\Models\TaskModel;

class TaskController
{
    public static function index(): void
    {
        Router::view("home", [
            "tasks" => TaskModel::fetchAll()
        ]);
    }

    public static function deleteTaskById(int $id): void
    {
        TaskModel::deleteById($id);
        self::index();
    }

    public static function createTask(string $title): void
    {
        TaskModel::createTask($title);
        self::index();
    }
}
