<?php

namespace App\Controllers;

use App\Routing\Router;
use App\Models\TaskModel;

class TaskController
{
    public static function index(): void
    {
        OAuthController::checkLogedIn();

        $owner_id = (int)$_SESSION["id"];

        Router::view("home", [
            "tasks" => TaskModel::fetchAll($owner_id)
        ]);
    }

    public static function fetchAll(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            OAuthController::checkLogedIn();
        }

        $owner_id = (int)$_SESSION["id"];
        echo json_encode(TaskModel::fetchAll($owner_id));
    }

    public static function deleteTaskById(): void
    {
        OAuthController::checkLogedIn();

        $req_payload = file_get_contents("php://input");
        $json_payload = json_decode($req_payload, true);

        if (!isset($json_payload["id"])) {
            // TODO: HANDLE ERROR
            echo "ERROR: no id provided";
            exit;
        }

        $id = $json_payload["id"];

        // TODO: CHECK IF THE TASK IS OWNED BY THE USER !!
        TaskModel::deleteById($id);
        echo self::fetchAll();
    }

    public static function createTask(): void
    {
        OAuthController::checkLogedIn();

        $req_payload = file_get_contents("php://input");
        $json_payload = json_decode($req_payload, true);

        if (!isset($json_payload["title"])) {
            echo "ERROR: no title provided";
            exit;
        }
        $title = $json_payload["title"];
        $owner = $_SESSION["id"];

        TaskModel::createTask($title, $owner);
        echo self::fetchAll();
    }

    public static function updateTaskById(): void
    {
        OAuthController::checkLogedIn();

        $req_payload = file_get_contents("php://input");
        $json_payload = json_decode($req_payload, true);

        if (!isset($json_payload["title"]) || !isset($json_payload["id"])) {
            echo "ERROR: no title/id provided";
            exit;
        }

        $title = $json_payload["title"];
        $id = $json_payload["id"];

        // TODO: CHECK IF THE TASK IS OWNED BY THE USER !!
        TaskModel::updateTask($id, $title);
        echo self::fetchAll();
    }
}
