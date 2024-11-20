<?php

namespace App\Controllers;

use App\Routing\Router;
use App\Models\TaskModel;

class TaskController
{
    public static function index(): void
    {
        $owner_id = (int)$_SESSION["id"];

        Router::view("home", [
            "tasks" => TaskModel::fetchAll($owner_id)
        ]);
    }

    public static function fetchAll(): void
    {
        $owner_id = (int)$_SESSION["id"];
        echo json_encode(TaskModel::fetchAll($owner_id));
    }

    public static function deleteTaskById(): void
    {
        $req_payload = file_get_contents("php://input");
        $json_payload = json_decode($req_payload, true);

        if (!isset($json_payload["id"])) {
            echo "ERROR: no id provided";
            exit;
        }

        $id         = (int)$json_payload["id"];
        $owner_id   = (int)$_SESSION["id"];

        TaskModel::deleteById($id, $owner_id);
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

        $title      = $json_payload["title"];
        $start_time = $json_payload["start_time"];
        $end_time   = $json_payload["end_time"];
        $owner_id   = (int)$_SESSION["id"];

        TaskModel::createTask(
            $title,
            $start_time,
            $end_time,
            $owner_id,
        );

        echo self::fetchAll();
    }

    public static function updateTaskById(): void
    {
        $req_payload = file_get_contents("php://input");
        $json_payload = json_decode($req_payload, true);

        if (!isset($json_payload["title"]) || !isset($json_payload["id"])) {
            echo "ERROR: no title/id provided";
            exit;
        }

        $title      = $json_payload["title"];
        $id         = (int)$json_payload["id"];
        $owner_id   = (int)$_SESSION["id"];

        TaskModel::updateTask($id, $title, $owner_id);
        echo self::fetchAll();
    }
}
