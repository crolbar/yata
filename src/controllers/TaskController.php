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
            exit("ERROR: no id provided");
        }

        $id         = (int)$json_payload["id"];
        $owner_id   = TaskController::getUserIdFromCookie();

        Notify::deleteCronJob($id);

        TaskModel::deleteById($id, $owner_id);
        self::fetchAll();
    }

    public static function createTask(): void
    {
        $req_payload = file_get_contents("php://input");
        $json_payload = json_decode($req_payload, true);

        if (!isset($json_payload["title"])) {
            exit("ERROR: no title provided");
        }

        $title      = $json_payload["title"];
        $start_time = (int)$json_payload["start_time"];
        $end_time   = (int)$json_payload["end_time"];
        $owner_id   = TaskController::getUserIdFromCookie();

        $cron_job_id = Notify::createCronJob($start_time, $title);

        TaskModel::createTask(
            $title,
            $start_time,
            $end_time,
            $owner_id,
            $cron_job_id
        );

        self::fetchAll();
    }

    public static function updateTaskById(): void
    {
        $req_payload = file_get_contents("php://input");
        $json_payload = json_decode($req_payload, true);

        if (!isset($json_payload["title"]) || !isset($json_payload["id"])) {
            exit("ERROR: no title/id provided");
        }

        $id         = (int)$json_payload["id"];
        $title      = $json_payload["title"];
        $start_time = (int)$json_payload["start_time"];
        $end_time   = (int)$json_payload["end_time"];
        $owner_id   = TaskController::getUserIdFromCookie();

        $cron_job_id = Notify::updateCronJob($id, $start_time, $title);

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
