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

    public static function fetchAll(): void
    {
        echo json_encode(TaskModel::fetchAll());
    }

    public static function deleteTaskById(): void
    {
        $req_payload = file_get_contents("php://input");
        $json_payload = json_decode($req_payload, true);

        if (!isset($json_payload["id"])) {
            // TODO: HANDLE ERROR
            echo "ERROR: no id provided";
            exit;
        }

        $id = $json_payload["id"];

        TaskModel::deleteById($id);
        echo self::fetchAll();
    }

    public static function createTask(): void
    {
        $req_payload = file_get_contents("php://input");
        $json_payload = json_decode($req_payload, true);

        if (!isset($json_payload["title"])) {
            echo "ERROR: no title provided";
            exit;
        }
        $title = $json_payload["title"];

        TaskModel::createTask($title);
        echo self::fetchAll();
    }
}
