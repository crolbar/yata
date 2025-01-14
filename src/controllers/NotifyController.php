<?php

namespace App\Controllers;

use App\Models\NotifyModel;

class NotifyController
{
    public static function update_fcm_device_token(): void
    {
        if (!isset($_POST["token"]) || !isset($_POST["sub"])) {
            exit('{"error": "not set token and sub"}');
        }

        $device_token = $_POST["token"];
        $sub = $_POST["sub"];

        NotifyModel::update_fcm_device_token_for($sub, $device_token);
    }

    public static function update_notification_status(): void
    {
        if (!isset($_POST["wants_notifications"]) || !isset($_POST["sub"])) {
            exit('{"error": "not set token and sub"}');
        }

        $wants_notifications = (bool)($_POST["wants_notifications"] == "true" ? true : false);
        $sub = $_POST["sub"];

        NotifyModel::update_notifications_for($sub, $wants_notifications);
    }

    public static function get_notification_status(): void
    {
        if (!isset($_POST["sub"])) {
            exit('{"error": "not set token and sub"}');
        }

        $sub = $_POST["sub"];

        $state = NotifyModel::get_status_of_notifications_for($sub);
        if ($state === false) {
            exit("error while fetching notification status");
        }

        echo json_encode(["wants_notifications" => $state]);
    }
}
