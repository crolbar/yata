<?php

namespace App\Controllers;

use App\Models\NotifyModel;

class NotifyController
{
    public static function update_fcm_device_token(): void
    {
        if (!isset($_POST["token"]) || !isset($_POST["sub"])) {
            echo '{"error": "not set token and sub"}';
            exit;
        }

        $device_token = $_POST["token"];
        $sub = $_POST["sub"];

        NotifyModel::update_fcm_device_token_for($sub, $device_token);
    }
}
