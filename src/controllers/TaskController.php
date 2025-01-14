<?php

namespace App\Controllers;

use App\Routing\Router;
use App\Models\TaskModel;
use App\Lib\Notify;

class TaskController
{
    public static function index(): void
    {
        Router::view("home");
    }

    public static function fetchAll(): void
    {
        $week_start_unix    = (int)$_SERVER["HTTP_X_WEEK_START"];
        $week_end_unix      = (int)$_SERVER["HTTP_X_WEEK_END"];
        $owner_id           = TaskController::getUserIdFromCookie();

        echo json_encode(TaskModel::fetchAll(
            $owner_id,
            $week_start_unix,
            $week_end_unix,
        ));
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
        $owner_id   = TaskController::getUserIdFromCookie();

        TaskModel::deleteById($id, $owner_id);
        self::fetchAll();
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
        $start_time = (int)$json_payload["start_time"];
        $end_time   = (int)$json_payload["end_time"];
        $owner_id   = TaskController::getUserIdFromCookie();

        TaskModel::createTask(
            $title,
            $start_time,
            $end_time,
            $owner_id,
        );

        Notify::send_notif("Created new task: \"$title\"", $title);

        self::fetchAll();
    }

    public static function updateTaskById(): void
    {
        $req_payload = file_get_contents("php://input");
        $json_payload = json_decode($req_payload, true);

        if (!isset($json_payload["title"]) || !isset($json_payload["id"])) {
            echo "ERROR: no title/id provided";
            exit;
        }

        $id         = (int)$json_payload["id"];
        $title      = $json_payload["title"];
        $start_time = (int)$json_payload["start_time"];
        $end_time   = (int)$json_payload["end_time"];
        $owner_id   = TaskController::getUserIdFromCookie();

        TaskModel::updateTask(
            $id,
            $title,
            $start_time,
            $end_time,
            $owner_id,
        );
        self::fetchAll();
    }


    public static function getUserIdFromCookie(): int
    {
        return (int)json_decode(
            base64_decode(
                $_COOKIE["user_data"]
            ),
            true
        )["id"];
    }
}
